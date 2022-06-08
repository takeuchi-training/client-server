<x-layouts.app>
    <main class="container">
      <div class="row my-5">
        <div class="card p-5 col col-lg-8 offset-lg-2">

          <form method="POST" action="/login">
            @csrf
            <h1 class="h3 mb-3 fw-normal">Please sign in - Client</h1>
        
            <div class="form-floating">
              <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="{{ old('email') }}">
              <label for="email">Email address</label>
              @error('email')
                  <div class="alert alert-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-floating">
              <input type="password" class="form-control" id="password" name="password" placeholder="Password">
              <label for="password">Password</label>
            </div>
        
            <div class="checkbox mb-3">
              <label>
                <input type="checkbox" value="remember_me"> Remember me
              </label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
          </form>
        </div>
      </div>
      </main>
</x-layouts.app>