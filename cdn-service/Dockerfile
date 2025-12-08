FROM golang:1.21-alpine AS builder
WORKDIR /app
COPY main.go .
RUN CGO_ENABLED=0 GOOS=linux go build -o cdn-server main.go

FROM alpine:latest
WORKDIR /root/
COPY --from=builder /app/cdn-server .

RUN mkdir uploads

EXPOSE 4000

CMD ["./cdn-server"]