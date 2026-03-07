using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class GachaRoundBandComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class GachaRoundBandSetting
        {
            public GachaType GachaType;
            public Sprite BandSprite;
            public string BandText;

        }
        [SerializeField] List<GachaRoundBandSetting> _gachaRoundBandSettings;
        [SerializeField] UIImage _bandImage;
        [SerializeField] UIText _bandText;

        public void Setup(GachaType gachaType)
        {
            var setting = _gachaRoundBandSettings.Find(x => x.GachaType == gachaType);

            if (setting == null)
            {
                Hidden = true;
                return;
            }

            _bandImage.Sprite = setting.BandSprite;
            _bandText.SetText(setting.BandText);
        }
    }
}
