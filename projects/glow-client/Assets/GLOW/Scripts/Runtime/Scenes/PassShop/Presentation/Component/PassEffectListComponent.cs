using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Scenes.PassShop.Presentation.Component
{
    public class PassEffectListComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct PassEffectInfo
        {
            public ShopPassEffectType effectType;
            public UIObject effectObject;
            public UIText effectDescriptionText;
            public string effectDescriptionSentence;
        }
        [SerializeField] PassEffectInfo[] _passEffectList;
        
        public void SetupPassEffectList(IReadOnlyList<PassEffectViewModel> effectViewModels)
        {
            foreach (var effect in effectViewModels)
            {
                var info = _passEffectList.Find(component => component.effectType == effect.PassEffectType);
                if (info.effectObject == null)
                {
                    continue;
                }
                
                info.effectObject.Hidden = false;

                var description = info.effectDescriptionSentence;
                info.effectDescriptionText.SetText(description, effect.PassEffectValue.ToString());
            }
        }
    }
}