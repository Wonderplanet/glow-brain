using System;
using System.Collections.Generic;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Calculator;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using ModestTree;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-5_ガシャ一覧画面
    /// </summary>
    public class GachaListView : UIView
    {
        [SerializeField] Transform _gachaBannerParent;
        [SerializeField] GachaBannerSizeLComponent _gachaBannerSizeLComponentPrefab;
        [SerializeField] FestivalGachaBannerComponent _festivalGachaBannerComponentPrefab;
        [SerializeField] TutorialGachaBannerComponent _tutorialGachaBannerComponentPrefab;
        [SerializeField] GachaRoundBandComponent _gachaRoundBandComponent;
        [SerializeField] MedalGachaComponent _medalGachaComponentPrefab;
        [SerializeField] AlwaysPresentGachasComponent _alwaysPresentGachasPrefab;
        [SerializeField] ScrollRect _scrollRect;
        [SerializeField] VerticalLayoutGroup _verticalLayoutGroup;
        [SerializeField] ChildScaler _childScaler;
        [SerializeField] CanvasGroup _canvasGroup;
        
        AlwaysPresentGachasComponent _alwaysPresentGachasComponent;
        TutorialGachaBannerComponent _tutorialGachaBannerComponent;
        List<GachaBannerSizeLComponent> _gachaBannerSizeLComponents = new List<GachaBannerSizeLComponent>();
        List<FestivalGachaBannerComponent> _festivalGachaBannerComponents = new List<FestivalGachaBannerComponent>();
        List<MedalGachaComponent> _medalGachaComponents = new List<MedalGachaComponent>();
        List<GachaRoundBandComponent> _gachaBandComponents = new List<GachaRoundBandComponent>();

        public void DestroyGachaBannerComponents()
        {
            // 各ガチャ帯表示の削除
            foreach (var component in _gachaBandComponents)
            {
                Destroy(component.gameObject);
            }
            _gachaBandComponents.Clear();

            // ガチャバナー表示の削除
            foreach (var component in _gachaBannerSizeLComponents)
            {
                Destroy(component.gameObject);
            }
            _gachaBannerSizeLComponents.Clear();
            
            // フェスガシャ表示の削除
            foreach (var component in _festivalGachaBannerComponents)
            {
                Destroy(component.gameObject);
            }
            _festivalGachaBannerComponents.Clear();

            // メダルガシャ表示の削除
            foreach (var component in _medalGachaComponents)
            {
                Destroy(component.gameObject);
            }
            _medalGachaComponents.Clear();

            // ノーマル・プレミアムガチャ表示の削除
            if (_alwaysPresentGachasComponent != null)
            {
                Destroy(_alwaysPresentGachasComponent.gameObject);
                _alwaysPresentGachasComponent = null;
            }

            // チュートリアルガシャ表示の削除
            if (_tutorialGachaBannerComponent != null)
            {
                Destroy(_tutorialGachaBannerComponent.gameObject);
                _tutorialGachaBannerComponent = null;
            }
        }

        public void SetGachaBannerViewModel(
            GachaType gachaType,
            IReadOnlyList<GachaBannerViewModel> models,
            Action<MasterDataId> bannerTapAction,
            Action<MasterDataId> infoButtonTapAction)
        {
            _gachaBandComponents.Add(CreateBandComponent(gachaType));
            _gachaBannerSizeLComponents.AddRange(CreateGachaBannerComponents(models, bannerTapAction, infoButtonTapAction));
        }
        
        public void CreateFestivalGachaBandComponent()
        {
            _gachaBandComponents.Add(CreateBandComponent(GachaType.Festival));
        }
        
        public FestivalGachaBannerComponent CreateFestivalGachaBannerComponent(
            FestivalGachaBannerViewModel viewModel,
            Action<MasterDataId> bannerTapAction,
            Action<MasterDataId> infoButtonTapAction)
        {
            var component = Instantiate(_festivalGachaBannerComponentPrefab, _gachaBannerParent);
            component.Setup(viewModel);
            component.OnTappedBanner = bannerTapAction;
            component.OnInfoButton = infoButtonTapAction;
            _festivalGachaBannerComponents.Add(component);
            
            return component;
        }

        public void SetMedalGachaBannerViewModel(
            IReadOnlyList<MedalGachaBannerViewModel> viewModels,
            Action<MasterDataId, GachaDrawType> showGachaConfirmDialogAction,
            Action<MasterDataId> infoAction)
        {
            _gachaBandComponents.Add(CreateBandComponent(GachaType.Medal));
            _medalGachaComponents.AddRange(CreateMedalGachaBannerComponents(viewModels, showGachaConfirmDialogAction, infoAction));
        }

        public void SetAlwaysPresentGachaViewModels(
            PremiumGachaViewModel premiumGacha,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel,
            Action<MasterDataId, GachaDrawType> showGachaConfirmDialogAction,
            Action<MasterDataId> gachaRatioAction,
            Action<MasterDataId> gachaDetailAction)
        {
            _gachaBandComponents.Add(CreateBandComponent(GachaType.Premium));
            _alwaysPresentGachasComponent = Instantiate(_alwaysPresentGachasPrefab, _gachaBannerParent);
            _alwaysPresentGachasComponent.Setup(
                premiumGacha, 
                heldAdSkipPassInfoViewModel, 
                showGachaConfirmDialogAction, 
                gachaRatioAction,
                gachaDetailAction);
        }

        public void ScrollByGachaId(MasterDataId gachaId)
        {
            // キャンバスのアルファを0にする
            _canvasGroup.alpha = 0f;

            DoAsync.Invoke(this, async ct =>
            {
                // 画面遷移時にスクロール位置を調整するため1フレーム待つ
                await UniTask.Delay(1, cancellationToken: ct);

                // 該当バナー位置を取得
                var banner = _gachaBannerSizeLComponents.Find(x => x.GachaId == gachaId);
                var fesBanner = _festivalGachaBannerComponents.Find(x => x.MstGachaId == gachaId);
                var medalGacha = _medalGachaComponents.Find(x => x.GachaId == gachaId);

                RectTransform targetRect = null;
                if (_alwaysPresentGachasComponent != null && _alwaysPresentGachasComponent.PremiumGachaId == gachaId)
                {
                    targetRect = _alwaysPresentGachasComponent.RectTransform;
                }
                else if (banner != null)
                {
                    targetRect = banner.RectTransform;
                }
                else if (fesBanner != null)
                {
                    targetRect = fesBanner.RectTransform;
                }
                else if(medalGacha != null)
                {
                    targetRect = medalGacha.RectTransform;
                }
                else
                {
                    // バナーがなかった場合最上段を表示する
                    _scrollRect.DOVerticalNormalizedPos(1f, 0f);
                }

                float normalizedPos = 1f;
                if (targetRect != null)
                {
                    normalizedPos = ScrollPositionCalculator.CalculateTargetPositionInScroll(_scrollRect, targetRect, _verticalLayoutGroup.padding.top);
                }
                _scrollRect.DOVerticalNormalizedPos(normalizedPos, 0f);

                // 表示が完了したのでアルファを1にする
                _canvasGroup.DOFade(1f, 0.15f).Play();
            });
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play(RefreshDisplay);
        }

        GachaRoundBandComponent CreateBandComponent(GachaType gachaType)
        {
            var bandComponent = Instantiate(_gachaRoundBandComponent, _gachaBannerParent);
            bandComponent.Setup(gachaType);
            return bandComponent;
        }

        List<GachaBannerSizeLComponent> CreateGachaBannerComponents(
            IReadOnlyList<GachaBannerViewModel> bannerModels,
            Action<MasterDataId> bannerTapAction,
            Action<MasterDataId> infoButtonTapAction)
        {
            var bannerComponents = new List<GachaBannerSizeLComponent>();

            foreach (var model in bannerModels)
            {
                var component = CreateBannerSizeL(model, bannerTapAction, infoButtonTapAction);
                bannerComponents.Add(component);
            }

            return bannerComponents;
        }

        GachaBannerSizeLComponent CreateBannerSizeL(
            GachaBannerViewModel model,
            Action<MasterDataId> bannerTapAction,
            Action<MasterDataId> infoButtonTapAction)
        {
            var component = Instantiate(_gachaBannerSizeLComponentPrefab, _gachaBannerParent);
            component.Setup(model);
            component.OnTappedBanner = bannerTapAction;
            component.OnInfoButton = infoButtonTapAction;
            return component;
        }


        List<MedalGachaComponent> CreateMedalGachaBannerComponents(
            IReadOnlyList<MedalGachaBannerViewModel> viewModels,
            Action<MasterDataId, GachaDrawType> showGachaConfirmDialogAction,
            Action<MasterDataId> infoButtonTapAction)
        {
            var bannerComponents = new List<MedalGachaComponent>();

            foreach (var model in viewModels)
            {
                var component = Instantiate(_medalGachaComponentPrefab, _gachaBannerParent);
                component.Setup(model, showGachaConfirmDialogAction, infoButtonTapAction);
                bannerComponents.Add(component);
            }

            return bannerComponents;
        }

        public void CreateTutorialGachaBannerViewModel(
            TutorialGachaBannerViewModel model,
            Action<MasterDataId> drawButtonTappedAction,
            Action<MasterDataId> lineupButtonTappedAction)
        {
            _tutorialGachaBannerComponent = Instantiate(_tutorialGachaBannerComponentPrefab, _gachaBannerParent);
            _tutorialGachaBannerComponent.Setup(model, drawButtonTappedAction, lineupButtonTappedAction);
        }

        public void StopScroll()
        {
            _scrollRect.vertical = false;
        }

        void RefreshDisplay()
        {
            // フェスガシャでスケール調整が必要な場合があるためリフレッシュを行う
            if (_festivalGachaBannerComponents.IsEmpty()) return;

            foreach (var component in _festivalGachaBannerComponents)
            {
                component.RefreshScale();
            }
        }
    }
}
