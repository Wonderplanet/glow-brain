using System.Runtime.CompilerServices;
using Debug = UnityEngine.Debug;
#if !UNITY_EDITOR
using System.Diagnostics;
#endif  // !UNITY_EDITOR

namespace WPFramework.Modules.Log
{
    public static class ApplicationLog
    {
#if !UNITY_EDITOR
        [Conditional("DEVELOPMENT_BUILD")]
#endif
        [MethodImpl(MethodImplOptions.AggressiveInlining)]
        public static void Log(string tag, string message)
        {
            if (string.IsNullOrEmpty(tag))
            {
                Debug.Log(message);
                return;
            }

            Debug.Log($"<color=green>[{tag}]</color> {message}");
        }

        [MethodImpl(MethodImplOptions.AggressiveInlining)]
        public static void LogWarning(string tag, string message)
        {
            if (string.IsNullOrEmpty(tag))
            {
                Debug.LogWarning(message);
                return;
            }

            Debug.LogWarning($"<color=yellow>[{tag}]</color> {message}");
        }

        [MethodImpl(MethodImplOptions.AggressiveInlining)]
        public static void LogError(string tag, string message)
        {
            if (string.IsNullOrEmpty(tag))
            {
                Debug.LogError(message);
                return;
            }

            Debug.LogError($"<color=red>[{tag}]</color> {message}");
        }
    }
}
