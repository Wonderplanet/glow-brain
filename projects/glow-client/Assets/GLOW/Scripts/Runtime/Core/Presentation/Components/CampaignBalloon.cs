using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Campaign;
using GLOW.Core.Presentation.Modules;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class CampaignBalloon : UIObject
    {
        [Serializable]
        class CampaignObject
        {
            [SerializeField] CampaignType _campaignType;
            [SerializeField] UIObject _icon;

            public CampaignType CampaignType => _campaignType;
            public UIObject Icon => _icon;
        }
        
        [SerializeField] CampaignObject[] _campaignObjects;
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _descriptionText;
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] UIObject _backGroundWithIcon;
        [SerializeField] UIObject _backGroundWithoutIcon;
        [SerializeField] Animator _animator;
        [SerializeField] CanvasGroup _canvasGroup;

        public Animator Animator => _animator;

        protected override void Awake()
        {
            base.Awake();
            
            // アニメーションでフェードインするのでalphaは0にしておく
            _canvasGroup.alpha = 0f;
        }

        public void SetUpContent(CampaignType campaignType)
        {
            foreach (var campaignObject in _campaignObjects)
            {
                if (campaignObject.Icon == null)
                {
                    continue;
                }
                campaignObject.Icon.Hidden = campaignObject.CampaignType != campaignType;
            }

            _backGroundWithIcon.Hidden = campaignType == CampaignType.ChallengeCount;
            _backGroundWithoutIcon.Hidden = campaignType != CampaignType.ChallengeCount;
        }

        public void SetUpTitleText(CampaignTitle title)
        {
            _titleText.SetText(title.Value);
        }

        public void SetUpDescriptionText(CampaignDescription description)
        {
            _descriptionText.SetText(description.Value);
        }

        public void SetRemainingTimeText(RemainingTimeSpan remainingTimeSpan)
        {
            var remainingTimeText = TimeSpanFormatter.FormatRemaining(remainingTimeSpan);
            _remainingTimeText.SetText(remainingTimeText);
        }
    }
}
