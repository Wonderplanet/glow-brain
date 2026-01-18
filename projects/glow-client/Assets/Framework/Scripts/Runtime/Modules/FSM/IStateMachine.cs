using System.Threading;

namespace WPFramework.Modules.FSM
{
    public interface IStateMachine : IStateTransition
    {
        void AddState(IState state);
        void Run(CancellationToken cancellationToken);
        void Stop();
    }
}
