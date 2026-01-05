using System;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.UI.UIEffect;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [Serializable]
    public class UIEffectInfo
    {
        public UIEffectId Id;
        public BaseUIEffectView Prefab;
    }
}

