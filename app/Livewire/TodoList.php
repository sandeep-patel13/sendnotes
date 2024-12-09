<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{

    use WithPagination;

    #[Rule('required|min:3|max:255')]
    public $name;
    public $search;

    public $editingTodoId;

    #[Rule('required|min:3|max:255')]
    public $editingTodoName;

    public function create(){
        $validated = $this->validateOnly('name');
        Todo::create($validated);
        $this->reset('name');
        session()->flash('success', 'Created.');
        $this->resetPage();
    }

    public function delete($todoId) {
        try{
            $todo = Todo::findOrFail($todoId);
            $todo->delete(); 
        } catch(\Exception $e) {
            session()->flash('error', 'Oops! can not delete');
            return;
        }
    }

    public function toggle(Todo $todo) {
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit(Todo $todo) {
        $this->editingTodoId = $todo->id;
        $this->editingTodoName = $todo->name; 
    }

    public function cancelEdit(){
        $this->reset('editingTodoId', 'editingTodoName');
    }

    public function update() {
        $this->validateOnly('editingTodoName');
        Todo::find($this->editingTodoId)->update([
           'name' => $this->editingTodoName 
        ]);
        $this->cancelEdit();
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(5)
        ]);
    }
}
