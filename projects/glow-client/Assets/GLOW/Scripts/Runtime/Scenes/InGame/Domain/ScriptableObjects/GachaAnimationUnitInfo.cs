using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.ValueObjects.Gacha;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.ScriptableObjects
{
    [CreateAssetMenu(fileName = "GachaAnimationUnitInfo", menuName = "GLOW/ScriptableObject/GachaAnimationUnitInfo")]
    public class GachaAnimationUnitInfo : ScriptableObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class GachaFukidashiSetting
        {
            public Sprite FukidashiSprite;
            public GachaFukidashiPosition FukidashiPosition;

        }
        public Sprite PictureSprite;
        public GachaFukidashiSetting FukidashiSetting1;
        public GachaFukidashiSetting FukidashiSetting2;
    }
}
