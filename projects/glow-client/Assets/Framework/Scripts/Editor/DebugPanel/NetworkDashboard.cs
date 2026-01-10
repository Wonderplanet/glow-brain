using DebugDashboard;
using UnityEditor;
using UnityEngine;
#if ENABLED_HTTP_LIBRARY_SIMULATION
using UnityHTTPLibrary.Simulation;
#endif  // ENABLED_HTTP_LIBRARY_SIMULATION

namespace WPFramework.DebugDashboard
{
    public class NetworkDashboard
    {
#if ENABLED_HTTP_LIBRARY_SIMULATION
        static bool enableControlInternetReachable = false;
#endif // ENABLED_HTTP_LIBRARY_SIMULATION

        [DebugPanel("UnityHTTPLibrary", 0)]
        static void OnDraw()
        {
#if ENABLED_HTTP_LIBRARY_SIMULATION
            // NOTE: リクエストを遅延させるためのスライダーを追加
            NetworkSimulator.GetPlugin<RequestResponseDelayPlugin>().Delay =
                EditorGUILayout.Slider("Request Delay", NetworkSimulator.GetPlugin<RequestResponseDelayPlugin>().Delay, 0f, 10f);
            if (GUILayout.Button("Reset Request Delay"))
            {
                NetworkSimulator.GetPlugin<RequestResponseDelayPlugin>().Reset();
            }

            // NOTE: 接続状態を選択するためのドロップダウンを追加
            enableControlInternetReachable = EditorGUILayout.Toggle("Control Reachable", enableControlInternetReachable);

            GUI.enabled = enableControlInternetReachable;
            var reachability = (NetworkReachability)EditorGUILayout.EnumPopup("Internet Reachability",
                NetworkSimulator.GetPlugin<NetworkReachablePlugin>().InternetReachability);

            if (enableControlInternetReachable)
            {
                NetworkSimulator.GetPlugin<NetworkReachablePlugin>().InternetReachability = reachability;
            }
            else
            {
                NetworkSimulator.GetPlugin<NetworkReachablePlugin>().ResetInternetReachability();
            }

            GUI.enabled = true;

#else // ENABLED_HTTP_LIBRARY_SIMULATION
            EditorGUILayout.HelpBox("ENABLED_HTTP_LIBRARY_SIMULATION を define Symbols に設定することにより利用することができます。", MessageType.Warning);
#endif // ENABLED_HTTP_LIBRARY_SIMULATION
        }
    }
}
