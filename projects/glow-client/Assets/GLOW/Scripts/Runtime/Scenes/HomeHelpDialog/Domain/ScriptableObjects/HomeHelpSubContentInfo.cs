using System;
using UnityEngine;

namespace GLOW.Scenes.HomeHelpDialog.Domain.ScriptableObjects
{
    [Serializable]
    public class HomeHelpSubContentInfo
    {
        [SerializeField] string _header;
        [SerializeField, TextArea(1,3)] string _article;

        public string Header
        {
            get => _header;
            set => _header = value;
        }

        public string Article
        {
            get => _article;
            set => _article = value;
        }
    }
}
