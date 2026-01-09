using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpOpponentDetail.Presentation.Views
{
    public class PvpOpponentDetailUnitIcon : UIObject
    {
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] CharacterColorIcon _colorIcon;
        [SerializeField] Image _unitIcon;
        [SerializeField] UIImage _rarityFrame;
        [SerializeField] UIText _unitLevel;
        [SerializeField] IconCharaGrade _unitGrade;

        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct UnitIconRarityFrame
        {
            public Rarity Rarity;
            public Sprite FrameSprite;
        }
        [SerializeField] List<UnitIconRarityFrame> _unitIconRarityFrames;

        public void SetUpUnitIcon(CharacterIconAssetPath unitIconAssetPath)
        {
            if (unitIconAssetPath.IsEmpty())
            {
                _unitIcon.gameObject.SetActive(false);
                return;
            }
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_unitIcon, unitIconAssetPath.Value);
        }

        public void SetUpRoleIcon(CharacterUnitRoleType characterUnitRoleType)
        {
            _roleIcon.SetupCharaRoleIcon(characterUnitRoleType);
        }

        public void SetUpColorIcon(CharacterColor characterColor)
        {
            _colorIcon.SetupCharaColorIcon(characterColor);
        }

        public void SetUpLevel(UnitLevel unitLevel)
        {
            _unitLevel.SetText(unitLevel.ToString());
        }

        public void SetUpGrade(UnitGrade unitGrade)
        {
            _unitGrade.SetGrade(unitGrade);
        }

        public void SetUpRarityFrame(Rarity rarity)
        {
            var rarityFrame = _unitIconRarityFrames.Find(frame => frame.Rarity == rarity);
            if (rarityFrame.FrameSprite != null)
            {
                _rarityFrame.Sprite = rarityFrame.FrameSprite;
            }
            else
            {
                ApplicationLog.LogError(nameof(PvpOpponentDetailUnitIcon), $"No frame sprite found for rarity: {rarity}");
            }
        }
    }
}
