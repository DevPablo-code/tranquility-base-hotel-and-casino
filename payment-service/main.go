package main

import (
	"encoding/json"
	"fmt"
	"log"
	"math/rand"
	"net/http"
	"time"
)

type PaymentRequest struct {
	CardNumber string  `json:"card_number"`
	Amount     float64 `json:"amount"`
	Currency   string  `json:"currency"`
	CVV        string  `json:"cvv"`
}

type PaymentResponse struct {
	Status        string `json:"status"`  
	TransactionID string `json:"transaction_id"`
	Message       string `json:"message,omitempty"`
	Timestamp     string `json:"timestamp"`
}

func processPayment(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var req PaymentRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, `{"status":"error", "message":"Invalid JSON"}`, 400)
		return
	}

	log.Printf("[GO-BANK] Processing: %.2f %s for card *%s", req.Amount, req.Currency, req.CardNumber[len(req.CardNumber)-4:])

	time.Sleep(time.Millisecond * 800)

	if len(req.CardNumber) < 12 {
		json.NewEncoder(w).Encode(PaymentResponse{
			Status:  "declined",
			Message: "Invalid Card Number",
		})
		return
	}

	if req.Amount > 10000 {
		json.NewEncoder(w).Encode(PaymentResponse{
			Status:  "declined",
			Message: "Limit Exceeded",
		})
		return
	}

	resp := PaymentResponse{
		Status:        "approved",
		TransactionID: fmt.Sprintf("TXN-GO-%d-MOON", rand.Int63()),
		Timestamp:     time.Now().Format(time.RFC3339),
	}

	log.Printf("[GO-BANK] Approved: %s", resp.TransactionID)
	json.NewEncoder(w).Encode(resp)
}

func main() {
	http.HandleFunc("/api/process-payment", processPayment)
	
	fmt.Println("Moon Bank running on port 3000...")
	if err := http.ListenAndServe(":3000", nil); err != nil {
		log.Fatal(err)
	}
}