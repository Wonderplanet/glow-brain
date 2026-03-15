using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public class SelectQuestUseCase
    {
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        /// <summary>
        /// 選択クエストが変更されたらtrueを返す
        /// </summary>
        public bool SelectQuest(MasterDataId mstQuestId)
        {
            if (mstQuestId == PreferenceRepository.CurrentHomeTopSelectMstQuestId) return false;
            
            PreferenceRepository.SetCurrentHomeTopSelectMstQuestId(mstQuestId);
            PreferenceRepository.AddSelectedMstQuestId(mstQuestId);

            //一度別のクエスト選択、その後最後にプレイしたクエストを選択すると、UX的にチグハグになりそう
            // なのでクエストが1度でも切り替わったら最後にプレイしたクエストはリセットする
            PreferenceRepository.SetLastPlayedMstStageId(MasterDataId.Empty);

            return true;
        }
    }
}

