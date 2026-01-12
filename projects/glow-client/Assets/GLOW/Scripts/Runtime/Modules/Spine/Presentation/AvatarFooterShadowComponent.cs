using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.Spine.Presentation
{
    public class AvatarFooterShadowComponent : UIComponent
    {
        [Serializable]
        public class ShadowSpriteSetting
        {
            [SerializeField] Sprite _sprite;
            [SerializeField] CharacterColor _color;

            public Sprite Sprite => _sprite;
            public CharacterColor Color => _color;
        }
        [SerializeField] Image _shadowImage;
        [SerializeField] List<ShadowSpriteSetting> _shadowSprites;

        public void Setup(CharacterColor color)
        {
            var setting = _shadowSprites.Find(s => s.Color == color);
            if (null == setting) return;

            _shadowImage.sprite = setting.Sprite;
        }
    }
}
