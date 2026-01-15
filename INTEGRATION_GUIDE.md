# WhatsApp & AI Integration Guide

## 📱 Part 1: WhatsApp Connection (Evolution API)

### Step 1: Evolution API Setup

Evolution API एक free, open-source WhatsApp API है जो आप self-host कर सकते हैं।

#### Option A: Docker Install (Recommended)

```bash
# Docker Compose file create करें
mkdir evolution-api && cd evolution-api
```

Create `docker-compose.yml`:

```yaml
version: '3.8'
services:
  evolution-api:
    image: atendai/evolution-api:latest
    container_name: evolution_api
    restart: always
    ports:
      - "8081:8080"
    environment:
      - SERVER_URL=http://localhost:8081
      - AUTHENTICATION_TYPE=apikey
      - AUTHENTICATION_API_KEY=your-secret-api-key-here
      - AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES=true
      - DATABASE_ENABLED=true
      - DATABASE_CONNECTION_URI=mongodb://mongodb:27017/evolution
      - DATABASE_CONNECTION_DB_PREFIX_NAME=evolution
    volumes:
      - evolution_instances:/evolution/instances
    depends_on:
      - mongodb

  mongodb:
    image: mongo:latest
    container_name: mongodb
    restart: always
    ports:
      - "27017:27017"
    volumes:
      - mongodb_data:/data/db

volumes:
  evolution_instances:
  mongodb_data:
```

```bash
# Start Evolution API
docker-compose up -d
```

#### Option B: Use Hosted Service

आप hosted Evolution API use कर सकते हैं:
- https://evolution-api.com (Official)
- https://codechat.dev
- या कोई भी Evolution API compatible service

---

### Step 2: Create WhatsApp Instance

Evolution API चलने के बाद:

```bash
# 1. Instance Create करें
curl -X POST "http://localhost:8081/instance/create" \
  -H "Content-Type: application/json" \
  -H "apikey: your-secret-api-key-here" \
  -d '{
    "instanceName": "datsun-bot",
    "qrcode": true,
    "integration": "WHATSAPP-BAILEYS"
  }'
```

```bash
# 2. QR Code Get करें
curl -X GET "http://localhost:8081/instance/connect/datsun-bot" \
  -H "apikey: your-secret-api-key-here"
```

QR Code मिलेगा → WhatsApp Mobile से scan करें → Connected!

---

### Step 3: Configure Webhook

WhatsApp messages को Laravel app में receive करने के लिए:

```bash
curl -X POST "http://localhost:8081/webhook/set/datsun-bot" \
  -H "Content-Type: application/json" \
  -H "apikey: your-secret-api-key-here" \
  -d '{
    "url": "http://your-laravel-app.com/api/webhook/whatsapp",
    "webhook_by_events": false,
    "webhook_base64": false,
    "events": [
      "MESSAGES_UPSERT",
      "MESSAGES_UPDATE",
      "CONNECTION_UPDATE"
    ]
  }'
```

> **Note:** Local development के लिए ngrok use करें:
> ```bash
> ngrok http 8080
> # फिर ngrok URL use करें webhook में
> ```

---

### Step 4: Laravel Settings में Configure करें

Admin Panel में जाएं: **Settings** page

| Field | Value |
|-------|-------|
| API URL | `http://localhost:8081` (या आपका Evolution API URL) |
| API Key | `your-secret-api-key-here` |
| Instance Name | `datsun-bot` |

---

## 🤖 Part 2: Google Gemini AI Integration

### Step 1: Get Gemini API Key

1. **Google AI Studio** जाएं: https://aistudio.google.com/

2. **Sign In** करें Google account से

3. **Get API Key** click करें (left sidebar में)

4. **Create API Key** → API key copy करें

> ⚠️ **Free Tier Limits:**
> - 60 requests per minute
> - 1 million tokens per day
> - Perfect for small-medium business

---

### Step 2: Test Your API Key

```bash
# Test Gemini API
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "contents": [{
      "parts": [{"text": "Say hello in Hindi"}]
    }]
  }'
```

Response आएगा:
```json
{
  "candidates": [{
    "content": {
      "parts": [{"text": "नमस्ते! (Namaste!)"}]
    }
  }]
}
```

---

### Step 3: Configure in Laravel

Admin Panel → **Settings** → AI Settings:

| Field | Value |
|-------|-------|
| Gemini API Key | `AIza...` (आपकी API key) |
| Gemini Model | `gemini-2.5-flash` (Recommended) |

---

### Available Gemini Models

| Model | Speed | Best For |
|-------|-------|----------|
| `gemini-2.5-flash` | ⚡ Fastest | Chat, Quick responses |
| `gemini-2.0-flash` | ⚡ Fast | General purpose |
| `gemini-1.5-pro` | 🐢 Slower | Complex reasoning |

---

## 🔧 Complete Setup Checklist

### WhatsApp Setup
- [ ] Evolution API install/access
- [ ] Instance create करें
- [ ] QR scan करके connect करें
- [ ] Webhook configure करें
- [ ] Laravel settings में details add करें

### AI Setup
- [ ] Google AI Studio से API key लें
- [ ] API key test करें
- [ ] Laravel settings में add करें

### Testing
- [ ] Test message भेजें WhatsApp पर
- [ ] Check करें Laravel में message आया
- [ ] AI response verify करें

---

## 🧪 Quick Test

WhatsApp connected होने के बाद, अपने registered number पर message भेजें:

```
"मुझे cabinet handles चाहिए"
```

Expected Response (AI generated):
```
नमस्ते! 🙏

मैं Rahul, Datsun Hardware से। Cabinet handles के लिए inquiry के लिए धन्यवाद!

हमारे पास कई models available हैं:
- Model 007, 008, 009...

कौन सा model देखना चाहेंगे? या मैं catalogue भेज दूं?
```

---

## 🆘 Troubleshooting

### WhatsApp Not Connecting
```bash
# Check instance status
curl -X GET "http://localhost:8081/instance/connectionState/datsun-bot" \
  -H "apikey: your-api-key"
```

### Messages Not Received
1. Check webhook URL accessible है
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify Evolution API logs

### AI Not Responding
1. Check API key valid है
2. Check rate limits
3. View Laravel error logs

---

## 📞 Support

- Evolution API Docs: https://doc.evolution-api.com/
- Gemini API Docs: https://ai.google.dev/docs
- Laravel Logs: `storage/logs/laravel.log`
