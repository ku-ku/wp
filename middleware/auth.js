export default async function ({route, store, redirect}){
    if (/(admin)+/.test(route.name)){
      return;
    }

    const user = await store.getters["user"];
    if (user.id < 1) {
        return redirect('/admin');
    }
}