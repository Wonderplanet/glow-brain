using System;
using System.Collections.Generic;
using System.Linq;
using BuildIntegration;
using UnityEditor;
using UnityEngine;

namespace WPFramework.BuildActions
{
    [CreateAssetMenu(menuName = "UnityBuildIntegration/Build Actions/Framework/Change Stack Trace Type", fileName = "ChangeStackTraceType")]
    public sealed class ChangeStackTraceTypeAction : BuildAction
    {
        [Serializable]
        public class LogSetting
        {
            public LogType logType;
            public StackTraceLogType stackTraceLogType;
        }

        [SerializeField]
        LogSetting[] _logSettings;

        public LogSetting[] LogSettings
        {
            get => _logSettings;
            set => _logSettings = value;
        }

        public override void ExecuteAction<T>(T buildProfile, BaseBuilder<T> builder)
        {
            SetStackTraceLogType();
        }

        public void SetStackTraceLogType()
        {
            SetStackTraceLogType(
                _logSettings.ToDictionary(settings => settings.logType, settings => settings.stackTraceLogType));
        }

        public void ResetStackTraceLogType()
        {
            _logSettings = Array.Empty<LogSetting>();

            var logSettingsTable = _logSettings
                .ToDictionary(settings => settings.logType, settings => settings.stackTraceLogType);
            foreach (LogType logType in Enum.GetValues(typeof(LogType)))
            {
                var stackTraceLogType = StackTraceLogType.ScriptOnly;
                // NOTE: すでに値がセットされているならばそちらを採用する
                if (logSettingsTable.TryGetValue(logType, out var value))
                {
                    stackTraceLogType = value;
                }
                logSettingsTable[logType] = stackTraceLogType;
            }

            _logSettings = logSettingsTable.Select(pair => new LogSetting
            {
                logType = pair.Key,
                stackTraceLogType = pair.Value
            }).ToArray();

            EditorUtility.SetDirty(this);
            AssetDatabase.SaveAssetIfDirty(this);
        }

        void SetStackTraceLogType(IReadOnlyDictionary<LogType, StackTraceLogType> logSettings)
        {
            foreach (var setting in logSettings)
            {
                PlayerSettings.SetStackTraceLogType(setting.Key, setting.Value);
                Debug.Log($"SetStackTraceLogType: {setting.Key} - {setting.Value}");
            }

            EditorUtility.SetDirty(this);
            AssetDatabase.SaveAssetIfDirty(this);
        }

        public void OnEnable()
        {
            // NOTE: サイズが変更されている場合、Enumの数が変更されたとみなしてリセットする
            //       LogTypeに関してはエディタ側で入力制御されているので数が変わったのであればリセットする
            if (_logSettings.Length == Enum.GetValues(typeof(LogType)).Length)
            {
                return;
            }

            ResetStackTraceLogType();
        }
    }
}
