# TCP 3-Way Handshake와 Sequence, Acknowledgment 번호의 동작 원리

## 🔁 TCP 3-Way Handshake 요약

TCP 연결은 다음의 3단계로 구성된 handshake로 시작됩니다:

1. **SYN** (클라이언트 → 서버): `Seq = x`
2. **SYN-ACK** (서버 → 클라이언트): `Seq = y`, `Ack = x + 1`
3. **ACK** (클라이언트 → 서버): `Seq = x + 1`, `Ack = y + 1`

이 과정은 **양쪽의 초기 Sequence Number를 교환하고 동기화(synchronize)**하기 위한 절차입니다.

---

## 🧠 Sequence Number(Seq) 증가 규칙

- **내가 보낸 데이터의 바이트 수만큼 Seq가 증가**함
- payload가 없는 순수 ACK 패킷은 Seq가 증가하지 않음
- 예외적으로 **SYN, FIN**은 각각 1 바이트로 간주되어 Seq가 1 증가함

| 패킷 종류 | Seq 증가 여부 | 설명 |
|-----------|----------------|------|
| SYN       | ✅ (+1)         | 시퀀스 공간에서 1바이트 차지 |
| FIN       | ✅ (+1)         | 연결 종료 표시, 1바이트 차지 |
| ACK만 있음| ❌ (0)          | 데이터 없음 |
| 데이터 있음| ✅ (+N)        | N바이트만큼 증가 |

---

## 🧠 Acknowledgment Number(Ack) 증가 규칙

- **상대방이 보낸 데이터 바이트 수만큼 증가**
- 즉, Ack = “상대방으로부터 몇 바이트까지 받았는지”를 의미

### 예시:

1. 클라이언트 → 서버: `Seq = 1000`, `SYN` → 서버는 Ack = 1001로 응답
2. 서버 → 클라이언트: `Seq = 5000`, `SYN + ACK` → 클라가 Ack = 5001로 응답
3. 이후 데이터 전송:
   - 클라가 `Seq = 1001`, 300B 데이터 전송 → 서버는 Ack = 1301

---

## 📌 Wireshark로 관찰 시 주의사항

- SYN, FIN은 payload가 없어도 Seq 번호가 1 증가됨
- ACK 패킷은 데이터가 없으면 Seq 번호가 그대로 유지됨
- 따라서 `3-way handshake의 마지막 ACK`와 `그 직후 HTTP 요청`은 **같은 Seq 번호를 가질 수 있음**

---

## ✅ 요약

| 항목 | 설명 |
|------|------|
| Seq | 내가 보낸 데이터 기준, 바이트 단위로 증가 |
| Ack | 상대가 보낸 데이터 기준, 바이트 단위로 증가 |
| SYN, FIN | 각각 1바이트로 간주되어 Seq가 +1 증가 |
| HTTP 요청이 handshake 마지막 ACK와 동일한 Seq를 가질 수 있음 | 이유: ACK는 데이터가 없기 때문에 Seq가 그대로 유지됨 |