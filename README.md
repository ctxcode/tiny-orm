
# TinyOrm (WIP)



## Syntax

```
User::select('id, firstname, lastname')->where('age', $age)->where('posts > 20 AND moderator', false)->orderBy('id DESC')->limit(10)->get();
User::select()->where('age', $age)->where('posts > 20 AND moderator', false)->orderBy('id DESC')->limit(10)->count();
User::update()->where('id = ', $id)->set([
    'firstname' => $firstname
]);
$id = User::insert([
    'firstname' => $firstname,
    'lastname' => $lastname,
]);
User::delete()->where('id = ', $id)->confirm();

User::select('firstname, name')->where('id = ', $id)->with(['comments'=> function($q) {
    $q->select('from_user_id, text')->where('likes > ', 20);
}])->first();
```