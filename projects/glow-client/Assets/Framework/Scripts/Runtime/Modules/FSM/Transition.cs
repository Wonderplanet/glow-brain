using System;

namespace WPFramework.Modules.FSM
{
    public class Transition : AbstractTransition<AbstractContext>
    {
        public Transition(Type type, AbstractContext context) : base(type, context)
        {
        }
    }
}
