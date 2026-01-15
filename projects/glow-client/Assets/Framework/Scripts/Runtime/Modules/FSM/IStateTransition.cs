using System;

namespace WPFramework.Modules.FSM
{
    public interface IStateTransition
    {
        void RequestTransition(Type stateType);
        void RequestTransition<TContext>(Type stateType, TContext context) where TContext : AbstractContext;
        void RequestTransition<TState, TOptions>(TOptions options) where TOptions : AbstractContext;
    }
}
