using GLOW.Core.Presentation.Components;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine.Unity;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationFloatingAvatarComponent : UIObject
    {
        [SerializeField] UISpineWithOutlineAvatar _spineAvatar;

        protected override void Awake()
        {
            base.Awake();
            SetEnable(false);
        }

        public void DisableAvatar()
        {
            _spineAvatar.gameObject.SetActive(false);
        }

        public void SetAvatar(SkeletonDataAsset asset, Vector3 avatarScale)
        {
            _spineAvatar.gameObject.SetActive(true);
            _spineAvatar.SetSkeleton(asset);
            _spineAvatar.SetAvatarScale(avatarScale);
            _spineAvatar.Animate(CharacterUnitAnimation.Wait.Name);
        }

        public void SetEnable(bool enable)
        {
            Hidden = !enable;
        }

        public void SetAvatarPosition(PointerEventData eventData)
        {
            _spineAvatar.transform.localPosition = ConvertScreenPointToLocalPoint(eventData);
        }

        public void OnBeginDragEvent(PointerEventData eventData)
        {
            SetEnable(true);
        }

        public void OnDragEvent(PointerEventData eventData)
        {
            _spineAvatar.transform.localPosition = ConvertScreenPointToLocalPoint(eventData);
        }

        public void OnEndDragEvent(PointerEventData eventData)
        {
            SetEnable(false);
        }

        Vector2 ConvertScreenPointToLocalPoint(PointerEventData eventData)
        {
            RectTransformUtility.ScreenPointToLocalPointInRectangle(this.RectTransform, eventData.position,
                eventData.enterEventCamera, out var localPosition);
            localPosition -= new Vector2(0, 50);    // 下アンカー基準で位置を調整する
            return localPosition;
        }
    }
}
