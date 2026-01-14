using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.FSM
{
    public abstract class AbstractState<T> : IState where T : AbstractContext
    {
        protected T Context { get; private set; }
        public IStateTransition StateTransition { get; set; }

        public virtual async UniTask OnEnter(CancellationToken cancellationToken)
        {
            await UniTask.CompletedTask;
        }

        public virtual async UniTask OnExit(CancellationToken cancellationToken)
        {
            await UniTask.CompletedTask;
        }

        public virtual void SetContext(AbstractContext abstractContext)
        {
            if (abstractContext is not T stateOptions)
            {
                return;
            }

            Context = stateOptions;
        }

        public virtual void OnUpdate()
        {
        }

        public virtual void OnCancelled()
        {
        }
    }
}
