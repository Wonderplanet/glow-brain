using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views
{
    public class EnhanceQuestScoreDetailListCell : UICollectionViewCell
    {
        [SerializeField] UIText _score;
        [SerializeField] UIText _rewardMultiplier;
        [SerializeField] UIObject[] _iconObjects;

        public void SetUpScore(EnhanceQuestMinThresholdScore score)
        {
            _score.SetText("{0} ~", score.ToDisplayString());
        }

        public void SetUpRewardMultiplier(ItemAmount rewardMultiplier)
        {
            _rewardMultiplier.SetText(rewardMultiplier.ToStringWithMultiplicationSeparated());
        }

        public void SetUpRewardIcon(CoinRewardSizeType coinRewardSizeType)
        {
            foreach (var icon in _iconObjects)
            {
                icon.gameObject.SetActive(false);
            }

            var index = (int)coinRewardSizeType;
            if (index < 0 || index >= _iconObjects.Length)
            {
                return;
            }

            var iconObject = _iconObjects[index];
            iconObject.gameObject.SetActive(true);
        }
    }
}
