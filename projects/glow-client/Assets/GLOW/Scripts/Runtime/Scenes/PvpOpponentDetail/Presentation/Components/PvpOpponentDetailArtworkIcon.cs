using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpOpponentDetail.Presentation.Views
{
    public class PvpOpponentDetailArtworkIcon : UIObject
    {
        [SerializeField] UIImage _artworkImage;
        [SerializeField] IconRarityImage _iconRarityImage;
        [SerializeField] IconGrade _iconGrade;

        public void SetArtworkImage(ArtworkAssetPath artworkIconAssetPath)
        {
            if (artworkIconAssetPath.IsEmpty())
            {
                _artworkImage.gameObject.SetActive(false);
                return;
            }
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_artworkImage.Image, artworkIconAssetPath.Value);
        }

        public void SetRarityImage(Rarity rarity)
        {
            _iconRarityImage.Setup(rarity);
        }

        public void SetGrade(ArtworkGradeLevel artworkGrade)
        {
            _iconGrade.SetGrade(artworkGrade.Value);
        }
    }
}
