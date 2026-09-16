import crypto from 'node:crypto';

const MERCHANT_ID = process.env.P24_MERCHANT_ID;
const POS_ID = process.env.P24_POS_ID || process.env.P24_MERCHANT_ID;
const CRC_KEY = process.env.P24_CRC_KEY;
const API_KEY = process.env.P24_API_KEY;
const IS_SANDBOX = (process.env.P24_MODE || 'sandbox') === 'sandbox';
const P24_BASE_URL = IS_SANDBOX 
  ? 'https://sandbox.przelewy24.pl' 
  : 'https://secure.przelewy24.pl';
  
function generateSign(sessionId, amount, currency, crc) {
  const jsonString = JSON.stringify({
    sessionId,
    merchantId: Number(MERCHANT_ID),
    amount: Number(amount),
    currency,
    crc
  });
  return crypto.createHash('sha384').update(jsonString, 'utf8').digest('hex');
}

export async function createP24Transaction({ amount, email, description, returnUrl }) {
  const amountInGrosze = Math.round(amount * 100);
  const sessionId = `order_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
  const currency = 'PLN';

  const sign = generateSign(sessionId, amountInGrosze, currency, CRC_KEY);

  const payload = {
    merchantId: Number(MERCHANT_ID),
    posId: Number(POS_ID),
    sessionId: sessionId,
    amount: amountInGrosze,
    currency: currency,
    description: description || 'Rezerwacja kortu',
    email: email || 'klient@example.com',
    country: 'PL',
    language: 'pl',
    urlReturn: returnUrl || 'http://localhost:3000/sukces',
    sign: sign
  };

  const authHeader = 'Basic ' + Buffer.from(`${POS_ID}:${API_KEY}`).toString('base64');

  try {
    const response = await fetch(`${P24_BASE_URL}/api/v1/transaction/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': authHeader
      },
      body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (data.data && data.data.token) {
      return {
        success: true,
        url: `${P24_BASE_URL}/trnRequest/${data.data.token}`,
        sessionId: sessionId
      };
    } else {
      console.error('Błąd P24:', data);
      return { success: false, error: data };
    }
  } catch (error) {
    console.error('Błąd połączenia z P24:', error);
    return { success: false, error: error.message };
  }
}