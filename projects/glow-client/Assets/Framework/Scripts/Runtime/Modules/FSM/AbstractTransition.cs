using System;

namespace WPFramework.Modules.FSM
{
    public abstract class AbstractTransition<T> where T : AbstractContext
    {
        public Type Type { get; }
        public T Context { get; }

        protected AbstractTransition(Type type, T context)
        {
            Type = type;
            Context = context;
        }
    }
}
