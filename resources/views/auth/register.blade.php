<x-layouts.app>
  <main class="container">
    <div class="row my-5">
      <div class="card p-5 col col-lg-8 offset-lg-2">

        <form method="POST" action="/register">
          @csrf
          <h1 class="h3 mb-3 fw-normal">Please sign up</h1>
      
          <div class="form-floating">
            <input type="name" class="form-control" id="name" name="name" placeholder="Your name">
            <label for="name">Name</label>
            @error('name')
                  <div class="alert alert-danger">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-floating">
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
            <label for="email">Email address</label>
            @error('email')
                  <div class="alert alert-danger">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-floating">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            <label for="password">Password</label>
            @error('password')
                  <div class="alert alert-danger">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-floating">
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Password">
            <label for="password_confirmation">Confirm password</label>
            @error('password_confirmation')
                  <div class="alert alert-danger">{{ $message }}</div>
            @enderror
          </div>
      
          <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Register</button>
        </form>
      </div>
    </div>
    </main>
</x-layouts.app>