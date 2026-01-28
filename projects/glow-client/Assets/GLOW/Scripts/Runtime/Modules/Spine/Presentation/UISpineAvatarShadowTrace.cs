using System;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Modules.Spine.Presentation
{
    /// <summary>
    /// UISpineAvatarかUISpineWithOutlineAvatarの影を追従するためのコンポーネント
    /// </summary>
    public class UISpineAvatarShadowTrace : MonoBehaviour
    {
        const string TraceBoneName = "all";
        const string TraceBoneNameSecondary = "All";
        [SerializeField] SkeletonGraphic _unitSkeleton;
        [SerializeField] RectTransform _shadowObj;

        Vector3 _defaultScale;

        void Awake()
        {
            _defaultScale = _shadowObj.localScale;
        }

        public void Update()
        {
            if(_unitSkeleton.skeletonDataAsset == null) return;
            var traceBone = _unitSkeleton.Skeleton.FindBone(TraceBoneName);
            if (null == traceBone)
            {
                traceBone = _unitSkeleton.Skeleton.FindBone(TraceBoneNameSecondary);
            }
            var skeletonSpacePosition = traceBone.GetSkeletonSpacePosition();
            var skeletonScale = _unitSkeleton.transform.localScale;

            var skeletonDataWidth = _unitSkeleton.SkeletonData.Width * skeletonScale.x;

            var shadowLocalPosition = _shadowObj.localPosition;
            shadowLocalPosition.x = skeletonSpacePosition.x * skeletonDataWidth / 2;
            _shadowObj.localPosition = shadowLocalPosition;

            // 素体は元のデータが大きくscaleで大幅に小さくしているため、それを外れ値として除外する
            if (skeletonScale.x < 0.5f) return;

            var shadowLocalScale = _shadowObj.localScale;
            shadowLocalScale.x = _defaultScale.x * skeletonScale.x;
            _shadowObj.localScale = shadowLocalScale;
        }

    }
}
