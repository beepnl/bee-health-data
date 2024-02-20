<template>
    <div>
        <div class="form-group">
            <label for="lastname">Last name*</label>
            <input v-model='author.lastname' type="text" class="form-control" maxlength="25" required />
        </div>

        <div class="form-group">
            <label for="initials">Initials*</label>
            <input v-model='author.initials' type="text" class="form-control" maxlength="25" required />
        </div>

        <div class="form-group">
            <label for="organisation">Organisation</label>
            <input v-model='author.organisation' type="text" class="form-control" maxlength="25" />
        </div>
    </div>
</template>

<script>

export default {
    props: ['author'],
}
</script>

