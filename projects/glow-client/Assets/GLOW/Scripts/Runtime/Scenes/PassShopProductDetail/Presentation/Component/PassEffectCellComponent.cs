using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.Component
{
    public class PassEffectCellComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct PassEffectInfo
        {
            public ShopPassEffectType EffectType;
            public Sprite EffectIconSprite;
            public string EffectDescription;
        }
        [SerializeField] PassEffectInfo[] _passEffectInformations;
        [SerializeField] PassEffectInfo _defaultPassEffectInformation;
        
        [SerializeField] UIImage _effectIconImage;
        [SerializeField] UIText _effectDescriptionLabel;
        
        public void SetupEffectCell(
            ShopPassEffectType shopPassEffectType,
            PassEffectValue passEffectValue)
        {
            var info = _passEffectInformations.FirstOrDefault(
                info => info.EffectType == shopPassEffectType, 
                _defaultPassEffectInformation);
            
            _effectIconImage.Sprite = info.EffectIconSprite;
            _effectDescriptionLabel.SetText(info.EffectDescription, passEffectValue.Value);
        }
    }
}