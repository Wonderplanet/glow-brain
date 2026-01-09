using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ExchangeShop.Domain.ValueObject;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeContentCell : UICollectionViewCell
    {
        [SerializeField] UIText _limitTimeText;
        [SerializeField] UIImage _bannerImage;
        [SerializeField] UIImage _grayOutImage;

        public void Setup(ExchangeContentCellViewModel viewModel, bool isOpening)
        {
            _grayOutImage.gameObject.SetActive(!isOpening);

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_bannerImage.Image, viewModel.BannerAssetPath.Value);

            if (viewModel.EndAt.IsUnlimited())
            {
                _limitTimeText.SetText("期限なし");
            }
            else
            {
                var limitText = TimeSpanFormatter.FormatUntilEnd(viewModel.LimitTime);
                _limitTimeText.SetText(limitText);
            }
        }
    }
}
