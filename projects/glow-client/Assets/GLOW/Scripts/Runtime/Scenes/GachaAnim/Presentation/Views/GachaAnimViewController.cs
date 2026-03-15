using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using UIKit;
using WonderPlanet.RandomGenerator;
using WonderPlanet.ResourceManagement;
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
            public List<GachaAnimationUnitInfo> UnitInfos;
        }

        [Inject] IGachaAnimViewDelegate _delegate;
        [Inject] IGachaAnimationUnitInfoContainer GachaAnimationUnitInfoContainer { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IRandomizer Randomizer { get; }
        [Inject] IAssetSource AssetSource { get; }

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
            ActualView.Setup(viewModel, container.UnitInfos, Randomizer, AssetSource);
        }

        GachaAnimationContainer GetUnitResources(GachaAnimViewModel viewModel)
        {
            var gachaAnimationUnitInfos = new List<GachaAnimationUnitInfo>();

            foreach (var model in viewModel.GashaAnimResultViewModelList)
            {
                GachaAnimationUnitInfo unitInfo = null;
                if (model.ResourceType == ResourceType.Unit)
                {
                    unitInfo = GachaAnimationUnitInfoContainer.GetGachaAnimationUnitInfo(model.GachaAnimationUnitInfoAssetPath);
                }
                gachaAnimationUnitInfos.Add(unitInfo);
            }

            var container = new GachaAnimationContainer
            {
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
