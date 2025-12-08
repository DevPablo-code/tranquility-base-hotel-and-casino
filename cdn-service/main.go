package main

import (
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"time"
)

const uploadPath = "./uploads"

func uploadHandler(w http.ResponseWriter, r *http.Request) {
		if r.Method != "POST" {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

		r.ParseMultipartForm(10 << 20)

		file, handler, err := r.FormFile("image")
	if err != nil {
		fmt.Println("Error Retrieving the File")
		http.Error(w, "Error retrieving file", http.StatusBadRequest)
		return
	}
	defer file.Close()

			ext := filepath.Ext(handler.Filename)
	newFileName := fmt.Sprintf("img_%d%s", time.Now().UnixNano(), ext)
	dstPath := filepath.Join(uploadPath, newFileName)

		dst, err := os.Create(dstPath)
	if err != nil {
		http.Error(w, "Error creating file on server", http.StatusInternalServerError)
		return
	}
	defer dst.Close()

		if _, err := io.Copy(dst, file); err != nil {
		http.Error(w, "Error saving file", http.StatusInternalServerError)
		return
	}

	log.Printf("[CDN] Uploaded successfully: %s", newFileName)

		w.Header().Set("Content-Type", "application/json")
		fmt.Fprintf(w, `{"status":"ok", "filename":"%s"}`, newFileName)
}

func main() {
	if _, err := os.Stat(uploadPath); os.IsNotExist(err) {
		os.Mkdir(uploadPath, 0755)
	}

	http.HandleFunc("/upload", uploadHandler)

	fs := http.FileServer(http.Dir(uploadPath))
	http.Handle("/images/", http.StripPrefix("/images/", fs))

	fmt.Println("Moon CDN running on port 4000...")
	log.Fatal(http.ListenAndServe(":4000", nil))
}