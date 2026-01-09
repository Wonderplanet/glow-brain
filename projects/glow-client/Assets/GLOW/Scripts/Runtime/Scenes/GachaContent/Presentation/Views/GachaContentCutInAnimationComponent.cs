using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaContent.Presentation.Views;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    enum GachaContentCutInAnimationType
    {
        Normal1,
        Normal2,
        Special
    }

    public class GachaContentCutInAnimationComponent : UIObject
    {
        [SerializeField] Animator _cutInAnimator;
        [SerializeField] UIImage _normalUnitImage;
        [SerializeField] UIImage _normalUnitShadowImage;
        [SerializeField] UIImage _normalUnitMaskImage;
        [SerializeField] UIImage _normalUnitImage2;
        [SerializeField] UIImage _normalUnitShadowImage2;
        [SerializeField] UIImage _normalUnitMaskImage2;
        [SerializeField] UIImage _specialUnitImage;
        [SerializeField] GachaContentSpecialCutInComponent _specialCutInComponent;

        GachaContentCutInAnimationType _currentType = GachaContentCutInAnimationType.Normal1;
        Action _onEndAnimationAction;

        public void OnEndAnimation()
        {
            if (_currentType == GachaContentCutInAnimationType.Normal1)
            {
                // 再生
                _cutInAnimator.Play("Gasha-Chara-Move", 0, 0);

                // 表情2に切替
                _normalUnitImage.Hidden = true;
                _normalUnitShadowImage.Hidden = true;
                _normalUnitMaskImage.Hidden = true;
                _normalUnitImage2.Hidden = false;
                _normalUnitShadowImage2.Hidden = false;
                _normalUnitMaskImage2.Hidden = false;
                _specialUnitImage.Hidden = true;

                _currentType = GachaContentCutInAnimationType.Normal2;
            }
            else if (_currentType == GachaContentCutInAnimationType.Normal2)
            {
                // 再生
                _cutInAnimator.Play("Gasha-Chara-Move", 0, 0);
                // 背景色の切替
                _specialCutInComponent.Hidden = false;

                // 必殺技に切替
                _normalUnitImage.Hidden = true;
                _normalUnitShadowImage.Hidden = true;
                _normalUnitMaskImage.Hidden = true;
                _normalUnitImage2.Hidden = true;
                _normalUnitShadowImage2.Hidden = true;
                _normalUnitMaskImage2.Hidden = true;
                _specialUnitImage.Hidden = false;

                _currentType = GachaContentCutInAnimationType.Special;
            }
            else if(_currentType == GachaContentCutInAnimationType.Special)
            {
                // 表情3が再生終了したので次のキャラへページング
                _onEndAnimationAction?.Invoke();

                // キャラが一体の場合ページングせず再生再開する
                if (_onEndAnimationAction == null)
                {
                    _cutInAnimator.Play("Gasha-Chara-Move", 0, 0);

                    // 背景色の切替
                    _specialCutInComponent.Hidden = true;

                    // 表情1に切替
                    _normalUnitImage.Hidden = false;
                    _normalUnitShadowImage.Hidden = false;
                    _normalUnitMaskImage.Hidden = false;
                    _normalUnitImage2.Hidden = true;
                    _normalUnitShadowImage2.Hidden = true;
                    _normalUnitMaskImage2.Hidden = true;
                    _specialUnitImage.Hidden = true;

                    _currentType = GachaContentCutInAnimationType.Normal1;
                }
            }
        }

        public void SetEndAnimationAction(Action action)
        {
            _onEndAnimationAction = action;
        }

        public void UpdateUnitInfo(GachaContentCutInAssetPath assetPath, Rarity rarity)
        {
            // ユニット情報の再設定
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_normalUnitImage.Image, assetPath.Value + "_1");
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_normalUnitShadowImage.Image, assetPath.Value + "_1");
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_normalUnitMaskImage.Image, assetPath.Value + "_1");
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_normalUnitImage2.Image, assetPath.Value + "_2");
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_normalUnitShadowImage2.Image, assetPath.Value + "_2");
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_normalUnitMaskImage2.Image, assetPath.Value + "_2");
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_specialUnitImage.Image, assetPath.Value + "_3");
            _specialCutInComponent.Rarity = rarity;
            // 再生
            _cutInAnimator.Play("Gasha-Chara-Move", 0, 0);

            // 背景色の切替
            _specialCutInComponent.Hidden = true;

            // キャラ表示リセット
            _normalUnitImage.Hidden = false;
            _normalUnitShadowImage.Hidden = false;
            _normalUnitMaskImage.Hidden = false;
            _normalUnitImage2.Hidden = true;
            _normalUnitShadowImage2.Hidden = true;
            _normalUnitMaskImage2.Hidden = true;
            _specialUnitImage.Hidden = true;

            _currentType = GachaContentCutInAnimationType.Normal1;
        }

        public void ReplayUnitCutInAnimation()
        {
            // 再生
            _cutInAnimator.Play("Gasha-Chara-Move", 0, 0);

            // 背景色の切替
            _specialCutInComponent.Hidden = true;

            // キャラ表示リセット
            _normalUnitImage.Hidden = false;
            _normalUnitShadowImage.Hidden = false;
            _normalUnitMaskImage.Hidden = false;
            _normalUnitImage2.Hidden = true;
            _normalUnitShadowImage2.Hidden = true;
            _normalUnitMaskImage2.Hidden = true;
            _specialUnitImage.Hidden = true;

            _currentType = GachaContentCutInAnimationType.Normal1;
        }

        public bool IsAnimationPlaying()
        {
            return _cutInAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime < 1.0f;
        }
    }
}
