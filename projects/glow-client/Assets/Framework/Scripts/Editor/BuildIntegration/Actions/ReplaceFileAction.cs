using System;
using System.IO;
using BuildIntegration;
using UnityEditor;
using UnityEngine;
using WPFramework.Modules.Log;
using Object = UnityEngine.Object;

namespace WPFramework.BuildActions
{
    [CreateAssetMenu(menuName = "UnityBuildIntegration/Build Actions/Framework/Replace File", fileName = "ReplaceFile")]
    public class ReplaceFileAction : BuildAction
    {
        [SerializeField] string _sourceFilePath;
        [SerializeField] string _destinationFilePath;
        [SerializeField] bool _includeMetaInMove;
        [SerializeField, TextArea] string _description;

        public string SourceFilePath => _sourceFilePath;
        public string SourceMetaFilePath => GetMetaFilePath(_sourceFilePath);
        public string DestinationFilePath => _destinationFilePath;
        public string DestinationMetaFilePath => GetMetaFilePath(_destinationFilePath);
        public bool IncludeMetaInMove => _includeMetaInMove;

        public override void ExecuteAction<T>(T buildProfile, BaseBuilder<T> builder)
        {
            var sourceFilePath = _sourceFilePath;
            var destinationFilePath = _destinationFilePath;
            var includeMetaInMove = _includeMetaInMove;

            // NOTE: Descriptionの内容をログに出力する
            ApplicationLog.Log(nameof(ReplaceFileAction), $"{name} Description: {_description}");

            if (string.IsNullOrEmpty(sourceFilePath) || string.IsNullOrEmpty(destinationFilePath))
            {
                throw new Exception("Source or Destination file path is empty.");
            }

            if (sourceFilePath == destinationFilePath)
            {
                throw new Exception("Source and Destination file path is same.");
            }

            if (!File.Exists(sourceFilePath))
            {
                throw new FileNotFoundException($"Source file is not found. Path: {sourceFilePath}");
            }

            if (ReplaceFile(sourceFilePath, destinationFilePath, includeMetaInMove))
            {
                ApplicationLog.Log(nameof(ReplaceFileAction), $"Replace file. Source: {sourceFilePath}, Destination: {destinationFilePath}");
            }
            else
            {
                ApplicationLog.LogError(nameof(ReplaceFileAction), $"Failed to replace file. Source: {sourceFilePath}, Destination: {destinationFilePath}");
            }
        }

        static bool ReplaceFile(string sourceFilePath, string destinationFilePath, bool includeMetaInMove)
        {
            // NOTE: パスに指定されたファイルをTempディレクトリにバックアップする
            if (!ReplaceFile(sourceFilePath, destinationFilePath))
            {
                ApplicationLog.LogError(nameof(ReplaceFileAction), $"Failed to backup file. Source: {sourceFilePath}, Destination: {destinationFilePath}");
                return false;
            }

            // NOTE: Metaファイルもコピーしたい場合に処理を通す
            if (!includeMetaInMove)
            {
                return true;
            }

            // NOTE: パスに指定されたメタファイルをTempディレクトリにバックアップする
            var sourceMetaFilePath = GetMetaFilePath(sourceFilePath);
            var destinationMetaFilePath = GetMetaFilePath(destinationFilePath);
            if (ReplaceFile(sourceMetaFilePath, destinationMetaFilePath))
            {
                return true;
            }

            ApplicationLog.LogError(nameof(ReplaceFileAction), $"Failed to backup meta file. Source: {sourceMetaFilePath}, Destination: {destinationMetaFilePath}");
            return false;
        }

        static string GetMetaFilePath(string filePath)
        {
            return $"{filePath}.meta";
        }

        static bool ReplaceFile(string sourceFilePath, string destinationFilePath)
        {
            if (!File.Exists(sourceFilePath))
            {
                return false;
            }

            if (File.Exists(destinationFilePath))
            {
                // NOTE: コピー先にファイルが存在する場合はバックアップを取る
                var backupPath = Path.Combine("Temp", destinationFilePath);
                var backupDirectory = Path.GetDirectoryName(backupPath);
                if (!Directory.Exists(backupDirectory))
                {
                    Directory.CreateDirectory(backupDirectory);
                }

                File.Copy(destinationFilePath, backupPath, true);
            }

            // NOTE: パスに指定されたファイルを指定されたディレクトリに移動する
            var destinationDirectory = Path.GetDirectoryName(destinationFilePath);
            if (!Directory.Exists(destinationDirectory))
            {
                Directory.CreateDirectory(destinationDirectory);
            }
            File.Copy(sourceFilePath, destinationFilePath, true);
            AssetDatabase.SaveAssets();
            // NOTE: Main Object Name '*****' does not match filename '*****'
            //       というワーニングが出るが次のステップで書き換えるのでスルーして問題ない
            AssetDatabase.Refresh();

            // NOTE: UnityObjectの場合にMainObjectNameを書き換えないといけない場合があるため対応をする
            var obj = AssetDatabase.LoadAssetAtPath<Object>(destinationFilePath);
            if (!obj)
            {
                return true;
            }

            // NOTE: Main Object Name '*****' does not match filename '*****' に対応するため
            //       MainObjectNameを書き換える（ファイル名とMainObjectNameが不一致だと発生する）
            using var serializedObject = new SerializedObject(obj);
            obj.name = Path.GetFileNameWithoutExtension(destinationFilePath);
            serializedObject.ApplyModifiedProperties();
            EditorUtility.SetDirty(obj);
            AssetDatabase.SaveAssetIfDirty(obj);
            AssetDatabase.Refresh();

            return true;
        }
    }
}
