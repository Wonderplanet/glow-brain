using System;

namespace WPFramework.Modules.Environment
{
    public class EnvironmentNotFoundException : Exception
    {
        public EnvironmentNotFoundException(string message) : base(message)
        {
        }
    }
}
