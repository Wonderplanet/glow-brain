using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public interface IHomeMainBannerItemViewDelegate
    {
        void OnBannerClicked(HomeBannerDestinationType destinationType, HomeBannerDestinationPath destionationPath);
    }
}
