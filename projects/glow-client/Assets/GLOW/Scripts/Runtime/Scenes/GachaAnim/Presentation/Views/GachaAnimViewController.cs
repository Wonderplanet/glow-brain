using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using WonderPlanet.RandomGenerator;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaAnim.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-3_ガシャ演出
    /// </summary>
    public class GachaAnimViewController: UIViewController<GachaAnimView>, IEscapeResponder
    {
        record GachaAnimationContainer
        {
            public List<UnitImage> UnitImages;
            public List<GachaAnimationUnitInfo> UnitInfos;
        }

        [Inject] IGachaAnimViewDelegate _delegate;
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] IGachaAnimationUnitInfoContainer GachaAnimationUnitInfoContainer { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IRandomizer Randomizer { get; }

        bool _isAnimationEnd = false;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);

            _delegate.OnViewDidLoad();
        }

        public void Setup(GachaAnimViewModel viewModel)
        {
            var container = GetUnitResources(viewModel);
            ActualView.Setup(viewModel, container.UnitImages, container.UnitInfos, Randomizer);
        }

        GachaAnimationContainer GetUnitResources(GachaAnimViewModel viewModel)
        {
            var unitImages = new List<UnitImage>();
            var gachaAnimationUnitInfos = new List<GachaAnimationUnitInfo>();

            foreach (var model in viewModel.GashaAnimResultViewModelList)
            {
                UnitImage unitImage = null;
                GachaAnimationUnitInfo unitInfo = null;
                if (model.ResourceType == ResourceType.Unit)
                {
                    var prefab = UnitImageContainer.Get(model.UnitImageAssetPath);
                    unitImage = prefab.GetComponent<UnitImage>();
                    unitInfo = GachaAnimationUnitInfoContainer.GetGachaAnimationUnitInfo(model.GachaAnimationUnitInfoAssetPath);
                }
                unitImages.Add(unitImage);
                gachaAnimationUnitInfos.Add(unitInfo);
            }

            var container = new GachaAnimationContainer
            {
                UnitImages = unitImages,
                UnitInfos = gachaAnimationUnitInfos
            };

            return container;
        }

        public void PlayGashaAnimation()
        {
            ActualView.PlayGashaAnimation(OnAnimationEnd);
        }

        public async UniTask WaitAnimation(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(() => _isAnimationEnd, cancellationToken: cancellationToken);
        }

        void OnAnimationEnd()
        {
            Dismiss();
            _isAnimationEnd = true;
        }

        [UIAction]
        void OnSkipButtonTapped()
        {
            ActualView.OnAllSkipButtonTapped();
        }

        [UIAction]
        void OnResultAnimButtonTapped()
        {
            ActualView.OnResultAnimSkipButtonTapped();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;
            // ガシャ演出中はバックキー無効のトースト表示
            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }
    }
}
