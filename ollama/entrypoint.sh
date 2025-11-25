#!/bin/bash

/bin/ollama serve &

pid=$!

echo "Waiting for Ollama..."
while ! curl -s http://localhost:11434/api/tags > /dev/null; do
    sleep 1
done

if ! curl -s http://localhost:11434/api/tags | grep -q "mark_receptionist"; then
    echo "Creating custom model 'mark_receptionist'..."

    ollama create mark_receptionist -f /root/.ollama/Modelfile
    echo "Model created!"
else
    echo "Model 'mark_receptionist' already exists."
fi

wait $pid