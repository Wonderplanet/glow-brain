using System;
using System.Collections.Generic;
using System.Reflection;
using DebugDashboard;
using Kyusyukeigo.Helper;
using UnityEditor;
using UnityEngine;

namespace WPFramework.DebugDashboard
{
    public class GameViewPanel
    {
        const string IsX2Prefs = "GameViewPanel.isX2";
        const string IsLandscapePrefs = "GameViewPanel.isLandscape";
        static GameViewSizeHelper.GameViewSize _currentSize;
        static double _lastViewChangeTime;

        static bool IsX2
        {
            get => EditorPrefs.GetBool(IsX2Prefs, false);
            set => EditorPrefs.SetBool(IsX2Prefs, value);
        }

        static bool IsLandscape
        {
            get => EditorPrefs.GetBool(IsLandscapePrefs, false);
            set => EditorPrefs.SetBool(IsLandscapePrefs, value);
        }

        [DebugPanel("GameViewSizeFitter", 0)]
        static void OnDraw()
        {
            IsX2 = EditorGUILayout.Toggle("size x2", IsX2);
            IsLandscape = EditorGUILayout.Toggle("Landscape", IsLandscape);

            foreach (var size in GameViewSizes())
            {
                var buttonText = $"{size.baseText}";
                if (!GUILayout.Button(buttonText))
                {
                    continue;
                }

                if (IsLandscape)
                {
                    (size.height, size.width) = (size.width, size.height);
                    size.baseText += " Landscape";
                }

                ChangeSize(size);
            }
        }

        static IEnumerable<GameViewSizeHelper.GameViewSize> GameViewSizes()
        {
            // NOTE: sizesにサイズをAddするとDebugDashboardにサイズ変更ボタンが追加されます
            var sizes = new List<GameViewSizeHelper.GameViewSize>
            {
                // NOTE: スクリプタブルオブジェクトなど外部ファイルから設定できると追加が簡単になりそうです
                new GameViewSizeHelper.GameViewSize
                {
                    type = GameViewSizeHelper.GameViewSizeType.FixedResolution,
                    height = 2208,
                    width = 1840,
                    baseText = "5:6"
                },
                new GameViewSizeHelper.GameViewSize
                {
                    type = GameViewSizeHelper.GameViewSizeType.FixedResolution,
                    height = 2048,
                    width = 1536,
                    baseText = "3:4"
                },
                new GameViewSizeHelper.GameViewSize
                {
                    type = GameViewSizeHelper.GameViewSizeType.FixedResolution,
                    height = 1336,
                    width = 640,
                    baseText = "9:16"
                },
                new GameViewSizeHelper.GameViewSize
                {
                    type = GameViewSizeHelper.GameViewSizeType.FixedResolution,
                    height = 2436,
                    width = 1125,
                    baseText = "9:19.5"
                },
                new GameViewSizeHelper.GameViewSize
                {
                    type = GameViewSizeHelper.GameViewSizeType.FixedResolution,
                    height = 3840,
                    width = 1644,
                    baseText = "9:21"
                },
                new GameViewSizeHelper.GameViewSize
                {
                    type = GameViewSizeHelper.GameViewSizeType.FixedResolution,
                    height = 2640,
                    width = 1080,
                    baseText = "9:22"
                },
            };

            return sizes.ToArray();
        }

        static void ChangeSize(GameViewSizeHelper.GameViewSize size)
        {
            GameViewSizeGroupType type;
            if (EditorUserBuildSettings.activeBuildTarget == BuildTarget.iOS)
            {
                type = GameViewSizeGroupType.iOS;
            }
            else if (EditorUserBuildSettings.activeBuildTarget == BuildTarget.Android)
            {
                type = GameViewSizeGroupType.Android;
            }
            else
            {
                type = GameViewSizeGroupType.Standalone;
            }

            GameViewSizeHelper.AddCustomSize(type, size);
            GameViewSizeHelper.ChangeGameViewSize(type, size);

            _currentSize = size;
            _lastViewChangeTime = EditorApplication.timeSinceStartup;
            EditorApplication.update += AdjustScaleDelayed;
        }

        static void AdjustScaleDelayed()
        {
            if (!(EditorApplication.timeSinceStartup > _lastViewChangeTime + 0.05f))
            {
                return;
            }

            AdjustScale(_currentSize);
            EditorApplication.update -= AdjustScaleDelayed;
        }

        static void AdjustScale(GameViewSizeHelper.GameViewSize size)
        {
            var asm = typeof(Editor).Assembly;
            var type = asm.GetType("UnityEditor.GameView");
            EditorWindow gameView = EditorWindow.GetWindow(type);
            var w = size.width / gameView.position.width;
            var h = size.height / (gameView.position.height - 20); // EditorGUI.kWindowToolbarHeight
            var a = Mathf.Min(1 / h, 1 / w);
            if (IsX2)
            {
                a *= 2;
            }
            var flag = BindingFlags.NonPublic | BindingFlags.Instance;
            type.GetMethod("SnapZoom", flag, null, new Type[] { typeof(float) }, null)
                .Invoke(gameView, new object[] { a });
        }
    }
}
