FROM golang:1.21-alpine AS builder
WORKDIR /app
COPY main.go .
RUN CGO_ENABLED=0 GOOS=linux go build -o bank-server main.go

FROM alpine:latest  
WORKDIR /root/
COPY --from=builder /app/bank-server .

EXPOSE 3000
CMD ["./bank-server"]