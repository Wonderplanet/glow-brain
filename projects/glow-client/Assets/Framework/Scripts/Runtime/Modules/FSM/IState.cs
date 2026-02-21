using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.FSM
{
    public interface IState
    {
        IStateTransition StateTransition { get; set; }
        UniTask OnEnter(CancellationToken cancellationToken);
        UniTask OnExit(CancellationToken cancellationToken);
        void SetContext(AbstractContext context);
        void OnUpdate();
        void OnCancelled();
    }
}
