using UnityEditor.Localization;
using UnityEngine;

namespace WPFramework.Localization
{
    public static class UnityLocalizationForceHideLocaleMenuInGameView
    {
        [RuntimeInitializeOnLoadMethod(RuntimeInitializeLoadType.BeforeSceneLoad)]
        static void OnBeforeSceneLoadRuntimeMethod()
        {
            // NOTE: UnityLocalizationが勝手にAddressablesを初期化するので
            //       AddressableManagementとバッティングするため
            //       初期化処理を呼ばないようにしておく
            //       この設定は個人設定(EditorPreferences)としてエディタに保存される
            LocalizationEditorSettings.ShowLocaleMenuInGameView = false;
        }
    }
}
