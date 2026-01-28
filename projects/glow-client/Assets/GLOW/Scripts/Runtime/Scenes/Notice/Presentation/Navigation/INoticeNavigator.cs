using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;

namespace GLOW.Scenes.Notice.Presentation.Navigation
{
    public interface INoticeNavigator
    {
        void ShowBasicShopTopView();
        void ShowPackShopTopView();
        void ShowPassShopTopView();
        void ShowGachaView(NoticeDestinationPathDetail pathDetail);
        void ShowContentTopView();
        void ShowPvpTopView();
        void ShowExchangeShopView(NoticeDestinationPathDetail pathDetail);
        void ShowUrl(DestinationScene destinationScene);
    }
}
