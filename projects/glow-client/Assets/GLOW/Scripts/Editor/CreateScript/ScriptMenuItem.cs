using System.Collections.Generic;
using UnityEngine;
using UnityEditor;
using System.IO;

public enum ScriptType
{
    IViewDelegate,
    Presenter,
    View,
    ViewController,
    ViewModel,
    UseCase,
    UseCaseModel,
    ViewInstaller,
}

public class ScriptMenuItem
{
    static string presenterTemplatePath = "81-Glow-UIPresenterScript-UIPresenter.cs.txt";
    static string iViewDelegateTemplatePath = "81-Glow-UIIViewDelegateScript-UIIViewDelegate.cs.txt";
    static string viewControllerTemplatePath = "81-Glow-UIViewControllerScript-UIViewController.cs.txt";
    static string viewTemplatePath = "81-Glow-UIViewScript-UIView.cs.txt";
    static string viewModelTemplatePath = "81-Glow-UIViewModelScript-UIViewModel.cs.txt";
    static string useCaseTemplatePath = "81-Glow-UseCaseScript-UseCase.cs.txt";
    static string useCaseModelTemplatePath = "81-Glow-UseCaseModelScript-UseCaseModel.cs.txt";
    static string viewInstallerTempletePath = "81-Glow-ViewInstallerScript-UIViewInstaller.cs.txt";

    [MenuItem("Assets/Create/GLOW/Scripts/CreatePresentationLayerBase", false, 41)]
    static void CreatePresentationLayerBaseScript()
    {
        var absolutePath = CreateScriptPath();

        if (absolutePath == "") return;

        CreateGlowScript(absolutePath, ScriptType.View);
        CreateGlowScript(absolutePath, ScriptType.ViewController);
        CreateGlowScript(absolutePath, ScriptType.ViewModel);
        CreateGlowScript(absolutePath, ScriptType.Presenter);
        CreateGlowScript(absolutePath, ScriptType.IViewDelegate);
    }

    [MenuItem("Assets/Create/GLOW/Scripts/CreateDomainLayerBase", false, 42)]
    static void CreateDomainLayerBaseScript()
    {
        var absolutePath = CreateScriptPath();

        if (absolutePath == "") return;

        CreateGlowScript(absolutePath, ScriptType.UseCase);
        CreateGlowScript(absolutePath, ScriptType.UseCaseModel);
    }

    [MenuItem("Assets/Create/GLOW/Scripts/CreateApplicationLayerBase", false, 43)]
    static void CreateApplicationLayerBaseScript()
    {
        var absolutePath = CreateScriptPath();

        if (absolutePath == "") return;

        CreateGlowScript(absolutePath, ScriptType.ViewInstaller);
    }

    static string CreateScriptPath()
    {
        var absolutePath = EditorUtility.SaveFilePanel(
            "保存先フォルダ選択",
            GetSelectedPathInProjects(),
            "Contents Name",
            "cs");

        if (absolutePath == "")
        {
            return "";
        }

        return absolutePath;
    }

    static void CreateGlowScript(
        string absolutePath,
        ScriptType type)
    {
        string filePath = GetFileTemplate(type);
        if (string.IsNullOrEmpty(filePath))
        {
            Debug.LogWarning("not found templates");
            return;
        }

        string tempTexts = File.ReadAllText(filePath);
        var className = Path.GetFileNameWithoutExtension(absolutePath);

        var fileName = Path.GetFileName(absolutePath);
        if(type == ScriptType.IViewDelegate) fileName = "I" + fileName.Replace(".cs", GetViewScriptName(type));
        else fileName = fileName.Replace(".cs", GetViewScriptName(type));
        var path = Path.GetDirectoryName(absolutePath);

        File.WriteAllText($"{path}/{fileName}", tempTexts.Replace("#SCRIPTNAME#", className));

        AssetDatabase.Refresh();
    }

    static string GetSelectedPathInProjects()
    {
        var paths = new List<string>();

        UnityEngine.Object[] selectedAssets = Selection.GetFiltered(
            typeof(UnityEngine.Object), SelectionMode.Assets);

        foreach (var item in selectedAssets)
        {
            var relativePath = AssetDatabase.GetAssetPath(item);

            if (!string.IsNullOrEmpty(relativePath))
            {
                var fullPath = Path.GetFullPath(Path.Combine(
                    Application.dataPath, Path.Combine("..", relativePath)));

                paths.Add(fullPath);
            }
        }
        return paths[0];
    }

    static string GetViewScriptName(ScriptType type)
    {
        return type switch
        {
            ScriptType.Presenter => "Presenter.cs",
            ScriptType.View => "View.cs",
            ScriptType.ViewController => "ViewController.cs",
            ScriptType.IViewDelegate => "ViewDelegate.cs",
            ScriptType.ViewModel => "ViewModel.cs",
            ScriptType.UseCase => "UseCase.cs",
            ScriptType.UseCaseModel => "UseCaseModel.cs",
            ScriptType.ViewInstaller => "ViewInstaller.cs",
        };
    }

    static string GetFileTemplate(ScriptType type)
    {
        string assetPath = Directory.GetCurrentDirectory();
        string[] filePath = { "" };
        filePath = type switch
        {
            ScriptType.Presenter => Directory.GetFiles(assetPath, presenterTemplatePath, SearchOption.AllDirectories),
            ScriptType.View => Directory.GetFiles(assetPath, viewTemplatePath, SearchOption.AllDirectories),
            ScriptType.ViewController => Directory.GetFiles(assetPath, viewControllerTemplatePath,
                SearchOption.AllDirectories),
            ScriptType.IViewDelegate =>
                Directory.GetFiles(assetPath, iViewDelegateTemplatePath, SearchOption.AllDirectories),
            ScriptType.ViewModel => Directory.GetFiles(assetPath, viewModelTemplatePath, SearchOption.AllDirectories),
            ScriptType.UseCase => Directory.GetFiles(assetPath, useCaseTemplatePath, SearchOption.AllDirectories),
            ScriptType.UseCaseModel => Directory.GetFiles(assetPath, useCaseModelTemplatePath,
                SearchOption.AllDirectories),
            ScriptType.ViewInstaller => Directory.GetFiles(assetPath, viewInstallerTempletePath,
                SearchOption.AllDirectories),
        };
        return filePath[0];
    }
}
