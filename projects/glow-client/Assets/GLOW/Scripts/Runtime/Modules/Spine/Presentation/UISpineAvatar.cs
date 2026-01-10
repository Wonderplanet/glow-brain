using System.Collections;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using Spine.Unity;
using WonderPlanet.ResourceManagement;

namespace GLOW.Modules.Spine.Presentation
{
    public class UISpineAvatar : UIObject
    {
        [SerializeField] SkeletonGraphic _spine;
        IAssetReference _assetRef;

        protected override void Awake()
        {
            base.Awake();
            _spine.gameObject.SetActive(false);
        }

        // IAssetSource source { get { return UIContext.Main.AssetSource; } }
        // public void SetModel(string assetPath, Action onComplete)
        // {
        //     Debug.Log($"assetPath: {assetPath}");
        //     source.GetAsset<SkeletonDataAsset>(assetPath)
        //         .Subscribe(skeletonDataAsset =>
        //         {
        //             skeletonDataAsset.Retain();
        //             assetRef = skeletonDataAsset;
        //             Build(skeletonDataAsset.Value);
        //             onComplete?.Invoke();
        //         })
        //         .AddTo(this);
        // }

        public bool RaycastTarget { get => _spine.raycastTarget; set => _spine.raycastTarget = value; }

        public void SetAvatarScale(Vector3 scale)
        {
            _spine.transform.localScale = scale;
        }

        public void SetSkeleton(SkeletonDataAsset skeleton)
        {
            _spine.gameObject.SetActive(true);
            Build(skeleton);
        }

        void Build(SkeletonDataAsset skeleton)
        {
            _spine.skeletonDataAsset = skeleton;
            _spine.Initialize(true);
        }

        public void Animate(string animationName, bool isLoop = true)
        {
            if (!_spine.Skeleton.Data.Animations.Exists(a => a.Name == animationName)) return;
            _spine.AnimationState.SetAnimation(0, animationName, isLoop);
        }

        public void Animate(string animationName, string nextAnimationName, bool isNextAnimationLoop = true)
        {
            StartCoroutine(Animates(animationName, nextAnimationName, isNextAnimationLoop));
        }

        IEnumerator Animates(string animationName, string nextAnimationName, bool isNextAnimationLoop)
        {
            var skeletonAnimation = _spine.Skeleton.Data.Animations.Find(a => a.Name == animationName);
            if (skeletonAnimation == null) yield break;
            _spine.AnimationState.SetAnimation(0, animationName, false);
            yield return new WaitForSeconds(skeletonAnimation.Duration);
            Animate(nextAnimationName, isNextAnimationLoop);
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();

            _assetRef?.Release();
        }
    }
}
