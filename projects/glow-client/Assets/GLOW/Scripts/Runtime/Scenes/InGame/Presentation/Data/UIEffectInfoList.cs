using System.Collections.Generic;
using UnityEngine;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [CreateAssetMenu(fileName = "UIEffectInfoList", menuName = "GLOW/ScriptableObject/UIEffectInfoList")]
    public class UIEffectInfoList : ScriptableObject
    {
        public List<UIEffectInfo> List;
    }
}

