using System;
using System.Collections.Generic;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using Spine;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class FieldUnitShadowTrace : MonoBehaviour
    {
        [Serializable]
        public class ShadowSpriteSetting
        {
            [SerializeField] Sprite _sprite;
            [SerializeField] CharacterColor _color;

            public Sprite Sprite => _sprite;
            public CharacterColor Color => _color;
        }

        [SerializeField] SpriteRenderer _shadowObj;
        [SerializeField] List<ShadowSpriteSetting> _shadowSprites;

        Vector3 _defaultScale;

        SkeletonAnimation _skeletonAnimation;
        Bone _traceTargetBone;

        void Awake()
        {
            _defaultScale = _shadowObj.transform.localScale;
        }

        public void RegisterSkeletonAnimation(SkeletonAnimation skeletonAnimation)
        {
            if (skeletonAnimation == null) return;
            if (skeletonAnimation.skeletonDataAsset == null) return;

            _skeletonAnimation = skeletonAnimation;
            var skeleton = _skeletonAnimation.Skeleton;
            _traceTargetBone = skeleton.FindBone("all") ?? skeleton.FindBone("All");
        }

        public void SetupShadowColor(CharacterColor color)
        {
            var setting = _shadowSprites.Find(s => s.Color == color);
            if (null == setting) return;

            _shadowObj.sprite = setting.Sprite;
        }

        public void Clear()
        {
            _skeletonAnimation = null;
            _traceTargetBone = null;
        }

        public void Update()
        {
            if (null == _traceTargetBone) return;
            var skeletonSpacePosition = _traceTargetBone.GetSkeletonSpacePosition();

            var shadowTransform = _shadowObj.transform;
            var shadowLocalPosition = shadowTransform.localPosition;
            var skeletonScale = _skeletonAnimation.transform.localScale;
            shadowLocalPosition.x = skeletonSpacePosition.x * skeletonScale.x;
            shadowTransform.localPosition = shadowLocalPosition;

            // 素体は元のデータが大きくscaleで大幅に小さくしているため、それを外れ値として除外する
            if (skeletonScale.x < 0.5f) return;

            var shadowScale = _shadowObj.transform.localScale;
            shadowScale.x = _defaultScale.x * skeletonScale.x;
            shadowScale.y = _defaultScale.y * skeletonScale.y;
            shadowTransform.localScale = shadowScale;
        }

        public void FadeIn(float duration)
        {
            _shadowObj.color = new Color(1, 1, 1, 0f);
            _shadowObj.DOFade(1.0f, duration);
        }

        public void FadeOut(float duration)
        {
            _shadowObj.color = Color.white;
            _shadowObj.DOFade(0.0f, duration);
        }
    }
}
