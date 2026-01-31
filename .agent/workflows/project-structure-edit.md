---
description: Rules for editing Project Structure code - Password protected
---

## ⚠️ Password Protection Rule

**IMPORTANT:** SuperAdmin > Project Structure related code mein koi bhi change karne se pehle password maangna MANDATORY hai.

### Protected Files:
- `app/Http/Controllers/SuperAdmin/ProjectStructureController.php`
- `resources/views/superadmin/project-structure/*` (all blade files)
- Routes containing `project-structure` in `routes/web.php`

### Password: `9773256235`

### Request Format:

Jab bhi in files mein change karna ho, pehle yeh format use karke password maango:

```
🔒 Project Structure Change Request

📍 Location: SuperAdmin > Project Structure > [specific page name]
📁 Files: [list of files being modified]
✏️ Change: [simple words mein kya change hoga]
🎯 Reason: [kyun change karna hai]

Password dein: ________
```

### Example:

```
🔒 Project Structure Change Request

📍 Location: SuperAdmin > Project Structure > Admins > Workflow > Global Questions
📁 Files: ProjectStructureController.php, global-questions.blade.php
✏️ Change: Global Questions section add karunga with demo fields aur references
🎯 Reason: User ne bola Global Questions bhi Product Questions jaisa add karo

Password dein: ________
```

### After Password Verified:
- Password match hone ke baad hi changes karo
- Password: `9773256235`

### Note:
Yeh rule Project Structure ka representation hai live project ka. Changes yahan karne se live project ki logic reflect hoti hai.
