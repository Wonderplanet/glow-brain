using System;
using System.IO;
using UnityEditor;
using UnityEditor.Callbacks;
using UnityEditor.iOS.Xcode;
using UnityEngine;

/// <summary>
/// iOSチート対策ポストビルドプロセス
/// </summary>

namespace AntiCheat
{
    public static class AntiCheatXcodeSetting
    {
        static readonly string libraryFile = "/libCrackProofiOS.a";
        static readonly string headerFile = "/CrackProofiOS.h";
        static readonly string toolInstallPath = "/Users/user1/CrackProof2.8.6.0";
        static readonly string errorNotifyCodePath = "Assets/GLOW/Vendor/AntiCheat/Editor/";
        static readonly string errorNotifyFile = "ErrorNotifyObj.m";

        static readonly string frameworkRunScriptName = "Run Script framework";
        static readonly string appRunScriptName = "Run Script app";

        static readonly string OptionRobust = "-robust";

        [PostProcessBuild]
        static void OnPostProcessBuild(BuildTarget buildTarget, string path)
        {
            if (buildTarget != BuildTarget.iOS) return;

            // 堅牢化オブションがない場合はここで終わる
            if (!ContainsCommandLine(OptionRobust)) return;

            // frameworkScriptのLoad
            string frameworkScript = File.ReadAllText("Assets/GLOW/Editor/AntiCheat/iOS/RunScript2860UnityFramework.txt");
            if (string.IsNullOrEmpty(frameworkScript)) throw new Exception("Failed to load RunScriptFramework.txt");

            // appScriptのLoad
            string appScript = File.ReadAllText("Assets/GLOW/Editor/AntiCheat/iOS/RunScript2860UnityApp.txt");
            if (string.IsNullOrEmpty(appScript)) throw new Exception("Failed to load RunScriptApp.txt");

            // PBXProjectの初期化
            var projectPath = path + "/Unity-iPhone.xcodeproj/project.pbxproj";
            PBXProject pbxProject = new PBXProject();
            pbxProject.ReadFromFile(projectPath);

            // frameworkに対して設定を変更する
            ChangeFrameworkSettings(pbxProject, path, toolInstallPath, frameworkScript);

            // AppTargetに対して設定を変更する
            ChangeAppSettings(pbxProject, path, appScript);

            pbxProject.WriteToFile(projectPath);
            Debug.Log("Completed addin Anti-Cheat code to Xcode Project!!!");
        }

        static bool ContainsCommandLine(string optionName)
        {
            foreach (var arg in Environment.GetCommandLineArgs())
            {
                if (arg.StartsWith(optionName))
                {
                    return true;
                }
            }

            return false;
        }

        static void ChangeFrameworkSettings(PBXProject project, string buildPath, string installPath, string frameworkScript)
        {
            string targetFrameworkGuid = project.GetUnityFrameworkTargetGuid();

            // UserScriptSandboxingを無効にする
            project.SetBuildProperty(targetFrameworkGuid, "ENABLE_USER_SCRIPT_SANDBOXING", "NO");

            // ライブラリを組み込む
            {
                // パスとファイルを設定
                string headerFilePath = installPath + headerFile;
                string libraryFilePath = installPath + libraryFile;
                // ファイルを追加
                project.AddFileToBuild(targetFrameworkGuid,
                    project.AddFile(headerFilePath, headerFilePath, PBXSourceTree.Source));
                project.AddFileToBuild(targetFrameworkGuid,
                    project.AddFile(libraryFilePath, libraryFilePath, PBXSourceTree.Source));
                // ビルド設定を追加
                project.AddBuildProperty(targetFrameworkGuid, "OTHER_LDFLAGS", "-u _CrackProof");
                // Ligrary Search Pathsに追加
                Debug.Log("Library Search Paths : " + installPath);
                project.AddBuildProperty(targetFrameworkGuid, "LIBRARY_SEARCH_PATHS", installPath);
            }

            // ヘッダファイル/エラー通知関数を追加する
            string sourcePath = errorNotifyCodePath + errorNotifyFile;
            string destPath = buildPath + "/" + errorNotifyFile;
            FileUtil.CopyFileOrDirectory(sourcePath, destPath);
            project.AddFileToBuild(targetFrameworkGuid,
                project.AddFile(destPath, errorNotifyFile, PBXSourceTree.Source));

            // アクティベーション処理を追加する
            project.AddShellScriptBuildPhase(targetFrameworkGuid, frameworkRunScriptName, "/bin/sh", frameworkScript);
        }

        static void ChangeAppSettings(PBXProject project, string path, string appScript)
        {
            string targetMainGuid = project.GetUnityMainTargetGuid();

            // UserScriptSandboxingを無効にする
            project.SetBuildProperty(targetMainGuid, "ENABLE_USER_SCRIPT_SANDBOXING", "NO");

            // アクティベーション処理を追加する
            project.AddShellScriptBuildPhase(targetMainGuid, appRunScriptName, "/bin/sh", appScript);
        }
    }
}
