using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using GLOW.Scenes.GachaAnim.Presentation.Views.Parts;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using UnityEngine;
using UnityEngine.AddressableAssets;
using UnityEngine.UI;
using WonderPlanet.RandomGenerator;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.GachaAnim.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-3_ガシャ演出
    /// </summary>
    public class GachaAnimView : UIView
    {
        [SerializeField] UIObject _background;
        [SerializeField] AssetReferenceGameObject _animStartPrefabReference;
        [SerializeField] Transform _animStartRoot;
        [SerializeField] GachaAnimResultComponent _animResult;
        [SerializeField] Button _allSkipButton;
        
        GachaAnimViewModel _viewModel;
        GachaAnimStartComponent _animStart;
        bool _isAllSkip;
        List<UnitImage> _unitImages = new List<UnitImage>();
        List<GachaAnimationUnitInfo> _unitInfos = new List<GachaAnimationUnitInfo>();
        IRandomizer _randomizer;

        protected override void OnDestroy()
        {
            base.OnDestroy();
            ReleaseAnimStart();
        }
        
        public void Setup(
            GachaAnimViewModel viewModel,
            List<UnitImage> unitImages,
            List<GachaAnimationUnitInfo> gachaAnimationUnitInfos,
            IRandomizer randomizer)
        {
            _viewModel = viewModel;
            _unitImages = unitImages;
            _unitInfos = gachaAnimationUnitInfos;
            _randomizer = randomizer;
        }

        public void PlayGashaAnimation(Action animationEnd)
        {
            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                _animStart = await LoadAndInstantiateAnimStart(cancellationToken);
                
                DisplayGachaAnimRarityUpVariation(_viewModel.GachaAnimStartViewModel.EndRarity);
                _animStart.Setup(_viewModel.GachaAnimStartViewModel, () => SetActiveAllSkipButton(true));
                _animStart.gameObject.SetActive(true);

                _background.Hidden = true;
                
                await _animStart.PlayAnimation(cancellationToken);

                // Resultアニメーション前に全スキップを行う際、新規獲得が無い場合はここで終了
                if (_isAllSkip && !_viewModel.GashaAnimResultViewModelList.Exists(x => x.NewFlg.Value))
                {

                    await _animStart.PlayAnimation(cancellationToken);
                    animationEnd();
                    return;
                }
                _animStart.gameObject.SetActive(false);

                var drawCount = _viewModel.GashaAnimResultViewModelList.Count;
                _animResult.gameObject.SetActive(true);
                for (int i = 0; i < drawCount; i++)
                {
                    var resultViewModel = _viewModel.GashaAnimResultViewModelList[i];

                    // 全スキップが押されており、Newで無い演出はスキップ　またはタップ時された場合スキップ
                    if (_isAllSkip && !resultViewModel.NewFlg.Value) continue;

                    _animResult.Setup(resultViewModel, _unitImages[i], _unitInfos[i]);
                    await _animResult.PlayAnimation(cancellationToken);
                }

                await _animResult.PlayEndAnimation(cancellationToken);
                _animResult.gameObject.SetActive(false);

                animationEnd();
            });
        }

        public void OnAllSkipButtonTapped()
        {
            // 全スキップをタップしたので非表示にする
            SetActiveAllSkipButton(false);
            _isAllSkip = true;
            _animStart.SkipAll();
            _animResult.SkipAll();
        }

        public void OnResultAnimSkipButtonTapped()
        {
            _animResult.OnAnimationSkip();
        }

        void SetActiveAllSkipButton(bool isActive)
        {
            _allSkipButton.gameObject.SetActive(isActive);
        }
        
        void DisplayGachaAnimRarityUpVariation(Rarity endRarity)
        {
            DisplayRandomComponent(_animStart.VariationRComponents);
            if(endRarity == Rarity.R) return;

            DisplayRandomComponent(_animStart.VariationSRComponents);
            if(endRarity == Rarity.SR) return;

            DisplayRandomComponent(_animStart.VariationSSRComponents);
            if(endRarity == Rarity.SSR) return;

            DisplayRandomComponent(_animStart.VariationURComponents);
        }

        void DisplayRandomComponent(GachaAnimRarityUpVariationComponent component)
        {
            var index = _randomizer.Range(0, component.RarityUpImageComponents.Count);
            component.DisplayRarityUpImagesByIndex(index);
        }

        async UniTask<GachaAnimStartComponent> LoadAndInstantiateAnimStart(CancellationToken cancellationToken)
        {
            await _animStartPrefabReference
                .LoadAssetAsync<GameObject>()
                .WithCancellation(cancellationToken);
            
            var prefab = ((GameObject)_animStartPrefabReference.Asset).GetComponent<GachaAnimStartComponent>();

            return Instantiate(prefab, _animStartRoot);
        }
        
        void ReleaseAnimStart()
        {
            if (_animStartPrefabReference.IsValid()) _animStartPrefabReference.ReleaseAsset();
        }
    }
}
