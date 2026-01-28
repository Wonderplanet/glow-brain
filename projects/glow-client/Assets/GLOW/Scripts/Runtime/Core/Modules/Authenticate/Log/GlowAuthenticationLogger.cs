using UnityHTTPLibrary;

namespace GLOW.Core.Modules.Authenticate.Log
{
    internal class GlowAuthenticationLogger
    {
        const string Identifier = "UnityHTTPLibrary.Authenticate";

        public static void Log(object message)
        {
            if (!ServerApiSharedConfig.LogEnable)
            {
                return;
            }
            UnityEngine.Debug.Log($"[{Identifier}] : {message}");
        }

        public static void LogWarning(object message)
        {
            UnityEngine.Debug.LogWarning($"[{Identifier}] : {message}");
        }
    }
}
