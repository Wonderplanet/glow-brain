using System;
using System.Collections;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaResult.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-4_ガシャ結果
    /// </summary>
    public class GachaResultView : UIView
    {
        const float ToConvertAnimationWaitTime = 0.2f;
        const float ConvertAnimationWaitTime = 0.8f;
        const float ConvertEffectSEWaitTime = 0.35f;
        [SerializeField] List<GachaResultIconComponent> _components;
        [SerializeField] UIText _convertText;
        [SerializeField] UIText _acquiredAvatarText;
        [SerializeField] UIText _nextText;
        [SerializeField] GameObject _buttonParent;
        [SerializeField] GameObject _tutorialButtonParent;
        [SerializeField] Button _reDrawButton;
        [SerializeField] GameObject _backgroundFesParent;
        [SerializeField] GameObject _backgroundPickupParent;
        [SerializeField] GameObject _backgroundFreeParent;
        [SerializeField] GameObject _backgroundTicketParent;
        [SerializeField] GameObject _backgroundNormalParent;
        [SerializeField] GameObject _backgroundMedalParent;
        [SerializeField] GameObject _framePurple;
        [SerializeField] GameObject _frameOrange;
        [SerializeField] Material[] _fontMaterials = { };

        Action _onCompleteCellAnimation;
        Action _onButtonActivated;
        GachaType _gachaType;
        bool _isPlayHighRaritySE;

        public void SetColorByGachaType(GachaType gachaType)
        {
            _gachaType = gachaType;
            _backgroundFesParent.SetActive(gachaType is GachaType.Festival or GachaType.PaidOnly);
            _backgroundPickupParent.SetActive(gachaType is GachaType.Pickup or GachaType.Tutorial);
            _backgroundFreeParent.SetActive(gachaType == GachaType.Free);
            _backgroundTicketParent.SetActive(gachaType == GachaType.Ticket);
            _backgroundNormalParent.SetActive(gachaType is GachaType.Normal or GachaType.Premium);
            _backgroundMedalParent.SetActive(gachaType == GachaType.Medal);
            _framePurple.SetActive(false);
            _frameOrange.SetActive(false);
            _convertText.Hidden = true;
            _acquiredAvatarText.Hidden = true;
            _nextText.Hidden = true;

            switch (gachaType)
            {
                case GachaType.Tutorial:
                case GachaType.Pickup:
                    _framePurple.SetActive(true);
                    SetFontMaterial(0);
                    break;
                case GachaType.Normal:
                case GachaType.Premium:
                    _frameOrange.SetActive(true);
                    SetFontMaterial(1);
                    break;
                case GachaType.PaidOnly:
                case GachaType.Festival:
                    _frameOrange.SetActive(true);
                    SetFontMaterial(2);
                    break;
                case GachaType.Ticket:
                    _frameOrange.SetActive(true);
                    SetFontMaterial(3);
                    break;
                case GachaType.Free:
                    _framePurple.SetActive(true);
                    SetFontMaterial(4);
                    break;
                case GachaType.Medal:
                    _framePurple.SetActive(true);
                    SetFontMaterial(1);
                    break;
            }
        }

        public void SetGachaDrawButton(DrawableFlag reDrawableFlag)
        {
            _reDrawButton.gameObject.SetActive(reDrawableFlag.Value);
            _reDrawButton.interactable = reDrawableFlag.Value;
        }

        public void SetIconModels(IReadOnlyList<GachaResultCellViewModel> viewModels,
            IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels,
            Action onCompleteCellAnimation,
            PreConversionResourceExistenceFlag existsPreConversionResource,
            bool isGetAvatar,
            Action<PlayerResourceIconViewModel> onCellTappedAction,
            Action onButtonActivated
            )
        {
            _onCompleteCellAnimation = onCompleteCellAnimation;
            _onButtonActivated = onButtonActivated;
            var  onCompleteAction = new Action(() => OnCompleteCellAnimationAction(convertedViewModels, isGetAvatar, existsPreConversionResource));
            for (var i = 0; i < _components.Count; i++)
            {
                if(viewModels.Count <= i)
                {
                    _components[i].Hidden = true;
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
            _convertText.Hidden = true;
            _nextText.Hidden = true;
            // アバター獲得テキストを表示する
            _acquiredAvatarText.Hidden = false;

            for (var i = 0; i < _components.Count; i++)
            {
                if(avatarViewModel.Count <= i)
                {
                    // 不要なセルを非表示にする
                    _components[i].Hidden = true;
                    continue;
                }

                _components[i].SetAvatarModel(avatarViewModel[i], i, avatarViewModel.Count, OnCompleteAvatarAnimationAction);
            }
        }

        // セルアニメーションスキップ
        public void SkipCellAnimation(IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, bool isEnd, PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            foreach (var component in _components)
            {
                component.SkipCellAnimation();
            }

            // 変換アニメーションを再生する
            PlayConvertAnimation(convertedViewModels, isEnd, existsPreConversionResource);
        }

        public void SkipAvatarCellAnimation()
        {
            foreach (var component in _components)
            {
                component.SkipCellAnimation();
            }

            // アバター表示スキップ後、ボタンを表示する
            ActivateButton();

        }

        public void StartAnimation()
        {
            foreach (var component in _components)
            {
                component.StartAnimation();
            }
        }

        void PlayConvertAnimation(IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, bool isGetAvatar, PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            _convertText.Hidden = !existsPreConversionResource;

            StartCoroutine(WaitForSecond(ToConvertAnimationWaitTime));

            for (var index = 0; index < _components.Count; index++)
            {
                if (_components[index].Hidden)
                {
                    break;
                }

                if (convertedViewModels[index].IsEmpty())
                {
                    continue;
                }

                var component = _components[index];
                if (!component.Hidden)
                {
                    component.PlayConvertAnimation();
                }
            }

            // 変換アニメーション中待機し、終了後のアクションを実行する
            StartCoroutine(WaitAndExecute(ConvertAnimationWaitTime, isGetAvatar, convertedViewModels, existsPreConversionResource));
        }

        IEnumerator WaitForSecond(float waitTime)
        {
            yield return new WaitForSeconds(waitTime);
        }

        IEnumerator WaitAndExecute(float waitTime, bool isGetAvatar, IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, PreConversionResourceExistenceFlag existsPreConversionResource)
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
            _onCompleteCellAnimation?.Invoke();

            // 変換アニメーションを無効化する
            StopConvertAnimation(convertedViewModels);

            // アバター獲得時は次へ表示、それ以外はボタン表示
            if (isGetAvatar)
            {
                _nextText.Hidden = false;
            }
            else
            {
                ActivateButton();
            }
        }

        void StopConvertAnimation(IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels)
        {
            for (var index = 0; index < _components.Count; index++)
            {
                var component = _components[index];
                if (component.Hidden)
                {
                    break;
                }

                if (convertedViewModels[index].IsEmpty())
                {
                    continue;
                }

                if (!component.Hidden)
                {
                    component.StopConvertAnimation();
                }
            }
        }

        void OnCompleteCellAnimationAction(IReadOnlyList<PlayerResourceIconViewModel> convertedViewModels, bool isGetAvatar,PreConversionResourceExistenceFlag existsPreConversionResource)
        {
            // PlayingPopupAnimation -> PlayingConvertAnimation
            _onCompleteCellAnimation?.Invoke();

            // かけら変換アニメーション
            PlayConvertAnimation(convertedViewModels, isGetAvatar, existsPreConversionResource);
        }

        void OnCompleteAvatarAnimationAction()
        {
            ActivateButton();
            _onCompleteCellAnimation?.Invoke();
        }

        void SetFontMaterial(int index)
        {
            if (index >= _fontMaterials.Length)
            {
                return;
            }

            _convertText.SetMaterial(_fontMaterials[index]);
            _acquiredAvatarText.SetMaterial(_fontMaterials[index]);
            _nextText.SetMaterial(_fontMaterials[index]);
        }

        void ActivateButton()
        {
            if (_gachaType == GachaType.Tutorial)
            {
                _tutorialButtonParent.SetActive(true);
                return;
            }

            _buttonParent.SetActive(true);
            _onButtonActivated?.Invoke();
        }

        void PlayHighRaritySoundEffect()
        {
            if (_isPlayHighRaritySE) return;
            _isPlayHighRaritySE = true;
            
            SoundEffectPlayer.Play(SoundEffectId.SSE_072_004);
        }
    }
}
