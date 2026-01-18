using System;
using UnityEngine;

namespace WPFramework.AssetReleaseKeySystem
{
    [Serializable]
    public sealed class NameConversionWordInfo
    {
        [SerializeField]
        string _word;

        [SerializeField]
        string _replaceWord;

        public string Word => _word;
        public string ReplaceWord => _replaceWord;
    }
}
