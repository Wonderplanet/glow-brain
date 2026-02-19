using System;
using System.Collections;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.GachaResult.Presentation;
using GLOW.Scenes.GachaResult.Presentation.Views;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaResult.Presentation.Component
{
    public class BoxGachaResultComponent : UIObject
    {
        const float ToConvertAnimationWaitTime = 0.2f;
        const float ConvertAnimationWaitTime = 0.8f;
        const float ConvertEffectSEWaitTime = 0.35f;
        
        [SerializeField] List<GachaResultIconComponent> _components;
        [SerializeField] UIText _convertText;
        [SerializeField] UIText _acquiredAvatarText;
        
        Action _onCompleteCellAnimationCallback;
        bool _isPlayHighRaritySE;
        
        public void SetIconModels(
            IReadOnlyList<GachaResultCellViewModel> viewModels, 
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels,
            PreConversionResourceExistenceFlag existsPreConversionResource,
            Action<PlayerResourceIconViewModel> onCellTappedAction,
            Action onCompleteCellAnimation)
        {
            _onCompleteCellAnimationCallback = onCompleteCellAnimation;
            var onCompleteAction = new Action(() => OnCompleteCellAnimationAction(
                convertedViewModels, 
                existsPreConversionResource));
            
            for (var i = 0; i < _components.Count; i++)
            {
                if (viewModels.Count <= i)
                {
                    _components[i].IsVisible = false;
                    continue;
                }

                _components[i].SetIconModel(
                    viewModels[i],
                    convertedViewModels[i],
                    i, 
                    viewModels.Count,
                    onCompleteAction,
                    onCellTappedAction,
                    PlayHighRaritySoundEffect);
            }
        }
        
        public void SetAvatarModel(IReadOnlyList<PlayerResourceIconViewModel> avatarViewModel)
        {
            _convertText.IsVisible = false;
            // アバター獲得テキストを表示する
            _acquiredAvatarText.IsVisible = true;

            for (var i = 0; i < _components.Count; i++)
            {
                if(avatarViewModel.Count <= i)
                {
                    // 不要なセルを非表示にする
                    _components[i].IsVisible = false;
                    continue;
                }

                _components[i].SetAvatarModel(
                    avatarViewModel[i],
                    i, 
                    avatarViewModel.Count, 
                    OnCompleteAvatarAnimationAction);
            }
        }
        
        public void StartAnimation()
        {
            foreach (var component in _components)
            {
                component.StartAnimation();
            }
        }
        
        public void SkipCellAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, 
            PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            foreach (var component in _components)
            {
                component.SkipCellAnimation();
            }

            // 変換アニメーションを再生する
            PlayConvertAnimation(
                convertedViewModels, 
                existsPreConversionResource);
        }

        public void SkipAvatarCellAnimation()
        {
            foreach (var component in _components)
            {
                component.SkipCellAnimation();
            }
            
            _onCompleteCellAnimationCallback?.Invoke();
        }
        
        void OnCompleteCellAnimationAction(
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, 
            PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            // PlayingPopupAnimation -> PlayingConvertAnimation
            _onCompleteCellAnimationCallback?.Invoke();

            // かけら変換アニメーション
            PlayConvertAnimation(
                convertedViewModels, 
                existsPreConversionResource);
        }
        
        void OnCompleteAvatarAnimationAction()
        {
            _onCompleteCellAnimationCallback?.Invoke();
        }
        
        void PlayConvertAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, 
            PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            _convertText.IsVisible = existsPreConversionResource;

            StartCoroutine(WaitForSecond(ToConvertAnimationWaitTime));

            for (var index = 0; index < _components.Count; index++)
            {
                if (!_components[index].IsVisible)
                {
                    break;
                }

                if (convertedViewModels[index].IsEmpty())
                {
                    continue;
                }

                var component = _components[index];
                if (component.IsVisible)
                {
                    component.PlayConvertAnimation();
                }
            }

            // 変換アニメーション中待機し、終了後のアクションを実行する
            StartCoroutine(WaitAndExecute(
                ConvertAnimationWaitTime, 
                convertedViewModels, 
                existsPreConversionResource));
        }
        
        IEnumerator WaitForSecond(float waitTime)
        {
            yield return new WaitForSeconds(waitTime);
        }

        IEnumerator WaitAndExecute(
            float waitTime, 
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, 
            PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            // かけら変換がある場合、アニメーション再生
            if (existsPreConversionResource)
            {
                // 指定秒数待機
                yield return new WaitForSeconds(ConvertEffectSEWaitTime);

                // 変換SE再生
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_005);

                // 指定秒数待機
                yield return new WaitForSeconds(waitTime - ConvertEffectSEWaitTime);
            }

            // 変換アニメーションを終了する PlayingConvertAnimation -> EndConvertAnimation
            _onCompleteCellAnimationCallback?.Invoke();

            // 変換アニメーションを無効化する
            StopConvertAnimation(convertedViewModels);
        }
        
        void StopConvertAnimation(IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels)
        {
            for (var index = 0; index < _components.Count; index++)
            {
                var component = _components[index];
                if (!component.IsVisible)
                {
                    break;
                }

                if (convertedViewModels[index].IsEmpty())
                {
                    continue;
                }

                if (component.IsVisible)
                {
                    component.StopConvertAnimation();
                }
            }
        }
        
        void PlayHighRaritySoundEffect()
        {
            if (_isPlayHighRaritySE) return;
            _isPlayHighRaritySE = true;
            
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_004);
        }
    }
}