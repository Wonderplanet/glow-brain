using GLOW.Scenes.PvpTop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpOpponentDetail.Presentation.Views
{
    /// <summary>
    /// 決闘
    ///   決闘Top
    ///     決闘対戦相手情報モーダル
    /// </summary>
    public class PvpOpponentDetailViewController : UIViewController<PvpOpponentDetailView>
    {
        public record Argument(PvpTopOpponentViewModel PvpTopOpponentViewModel);

        [Inject] IPvpOpponentDetailViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUp(PvpTopOpponentViewModel opponentViewModel)
        {
            ActualView.SetUpUnitIcon(opponentViewModel.CharacterIconAssetPath);
            ActualView.SetUpEmblem(opponentViewModel.EmblemIconAssetPath);
            ActualView.SetUpVictoryPoint(opponentViewModel.Point);
            ActualView.SetUpUserName(opponentViewModel.UserName);
            ActualView.SetUpTotalPoint(opponentViewModel.TotalPoint);
            ActualView.SetUpTotalPartyStatus(opponentViewModel.TotalPartyStatus);
            ActualView.SetUpTotalPartyStatusUpperArrowFlag(opponentViewModel.TotalPartyStatusUpperArrowFlag);
            ActualView.SetUpUnitIcons(opponentViewModel.PartyUnits);
            ActualView.SetUpPvpRankIcon(opponentViewModel.PvpUserRankStatus);

            ActualView.PlayPlayerInfoAppearanceAnimation();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            Dismiss();
        }
    }
}
