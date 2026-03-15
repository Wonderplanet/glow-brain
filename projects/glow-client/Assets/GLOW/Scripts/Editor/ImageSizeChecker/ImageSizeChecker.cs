using System;
using System.Collections.Generic;
using System.Linq;
using UnityEditor;
using UnityEngine;

namespace GLOW.Editor.ImageSizeChecker
{
    public record ImageSizeModel(string Path, int Width, int Height)
    {
        public string Path { get; } = Path;
        public int Width { get; } = Width;
        public int Height { get; } = Height;
    }

    public class ImageSizeChecker
    {
        const string CheckStartTextFormat = "<color=green>==Start checking image size==</color> \n SIZE: {0} / FOLDER: {1}";
        const string CheckEndText = "<color=green>==Complete image size==</color>";
        public static void CheckImageSize(string targetFolder, int size)
        {
            Debug.Log(string.Format(CheckStartTextFormat, size, targetFolder));
            string[] guids = AssetDatabase.FindAssets("t:texture", new [] { targetFolder });

            try
            {
                var overSizeImages = guids.ToList().Select(guid =>
                {
                    var path = AssetDatabase.GUIDToAssetPath(guid);
                    var s =  GetTextureSize(path);
                    return new ImageSizeModel(path, s.width, s.height);
                })
                .Where(model => size < model.Width || size < model.Height)
                .ToList();
                ShowResults(overSizeImages, overSizeImages.Count);
            }
            catch(Exception e)
            {
                Debug.LogError(e);
            }

            Debug.Log(CheckEndText);
        }

        static (int width, int height) GetTextureSize(string path)
        {
            var texture = AssetDatabase.LoadAssetAtPath<Texture>(path);
            return (texture.width, texture.height);
        }

        public static void ShowResults(IReadOnlyList<ImageSizeModel> models, int missingCount)
        {
            Debug.Log($"{missingCount}件、指定サイズを超過したアセットが見つかりました");
            foreach (var model in models)
            {
                Debug.Log("pngパス: " + model.Path);
            }
        }
    }
}
