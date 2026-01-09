using System;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.HomeHelpDialog.Domain.ScriptableObjects
{
    [Serializable]
    public class HomeHelpMainContentInfo
    {
        [SerializeField] string _header;
        [SerializeField] List<HomeHelpSubContentInfo> _subContentInfoList;

        public string Header
        {
            get => _header;
            set => _header = value;
        }

        public IReadOnlyList<HomeHelpSubContentInfo> SubContentInfoList
        {
            get => _subContentInfoList;
            set => _subContentInfoList = (List<HomeHelpSubContentInfo>)value;
        }
    }
}
