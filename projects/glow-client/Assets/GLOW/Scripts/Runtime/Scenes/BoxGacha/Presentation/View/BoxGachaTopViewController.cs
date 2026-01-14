using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Presentation.View
{
    public class BoxGachaTopViewController : UIViewController<BoxGachaTopView>, IEscapeResponder
    {
        public record Argument(BoxGachaTopViewModel ViewModel, MasterDataId MstEventId);
        
        [Inject] IBoxGachaTopViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.InitializeRewardListView();
            ViewDelegate.OnViewDidLoad();
            
            EscapeResponderRegistry.Bind(this, ActualView);
        }
        
        public void SetUpBoxGachaInfo(BoxGachaInfoViewModel boxGachaInfoViewModel)
        {
            ActualView.SetUpRewardListView(
                boxGachaInfoViewModel.BoxGachaRewardListCellViewModels,
                OnPrizeIconTapped);
            ActualView.SetUpCostItemInfo(boxGachaInfoViewModel.CostResourceIconViewModel);
            ActualView.SetUpCurrentBoxInfoText(boxGachaInfoViewModel.BoxResetCount);
            ActualView.SetUpCurrentStockText(
                boxGachaInfoViewModel.CurrentBoxTotalDrawnCount, 
                boxGachaInfoViewModel.TotalStockCount);
            ActualView.SetUpDrawButtonCostInfo(
                boxGachaInfoViewModel.CostResourceIconViewModel, 
                boxGachaInfoViewModel.CostAmount);
            ActualView.SetUpRemainingTimeSpan(boxGachaInfoViewModel.RemainingTimeSpan);
        }
        
        public void ResetRewardListScrollPosition()
        {
            ActualView.ResetScrollPosition();
        }
        
        public void SetUpBoxGachaTopBackground(KomaBackgroundAssetPath komaBackgroundAssetPath)
        {
            ActualView.SetUpBackgroundImage(komaBackgroundAssetPath);
        }

        public void SetUpDecoUnitImage(
            UnitImageAssetPath unitFirstImageAssetPath,
            UnitImageAssetPath unitSecondImageAssetPath)
        {
            ActualView.SetUpDecoUnitImage(
                unitFirstImageAssetPath,
                unitSecondImageAssetPath,
                InstantiateCharacterImage(unitFirstImageAssetPath),
                InstantiateCharacterImage(unitSecondImageAssetPath));
        }

        public async UniTask PlayLineupResetInAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayLineupResetInAnimation(cancellationToken);
        }
        
        public async UniTask PlayLineupResetOutAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayLineupResetOutAnimation(cancellationToken);
        }
        
        public void SetButtonInteractable(bool interactable)
        {
            ActualView.SetButtonInteractable(interactable);
        }
        
        public bool OnEscape()
        {
            if (ActualView.Hidden) return false;
            
            ViewDelegate.OnCloseButtonTapped();
            return true;
        }
        
        UnitImage InstantiateCharacterImage(UnitImageAssetPath imageAssetPath)
        {
            if(imageAssetPath.IsEmpty())
            {
                return null;
            }

            var go = UnitImageContainer.Get(imageAssetPath);
            var characterImage = go.GetComponent<UnitImage>();
            characterImage.SortingOrder = 0;
            return characterImage;
        }
        
        void OnPrizeIconTapped(PlayerResourceIconViewModel prizeIconViewModel)
        {
            ViewDelegate.OnPrizeIconTapped(prizeIconViewModel);
        }
        
        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
        
        [UIAction]
        void OnBoxGachaLineupButtonTapped()
        {
            ViewDelegate.OnBoxGachaLineupButtonTapped();
        }
        
        [UIAction]
        void OnBoxGachaResetButtonTapped()
        {
            ViewDelegate.OnBoxGachaResetButtonTapped();
        }
        
        [UIAction]
        void OnBoxGachaDrawButtonTapped()
        {
            ViewDelegate.OnBoxGachaDrawButtonTapped();
        }
    }
}