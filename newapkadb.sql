```sql
ALTER TABLE bookflights
ADD commission_earned DECIMAL(10,2) NULL AFTER duration,
ADD segments JSON NULL AFTER commission_earned;
```