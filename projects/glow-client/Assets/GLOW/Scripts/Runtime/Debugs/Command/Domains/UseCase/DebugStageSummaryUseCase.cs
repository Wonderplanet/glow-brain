using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.DebugStageDetail.Domain;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugStageSummaryUseCaseModel(
        SortOrder SortOrder,
        MasterDataId MstEventIdForSort,
        MasterDataId MstId, //MstQusetId or MstAdventBattleId or MstPvpId
        QuestName QuestName, //降臨バトルの名前もここに入る
        EventName EventName, //降臨バトルのときは"降臨バトル"と入る
        Difficulty Difficulty,
        DebugStageDetailQuestType DebugQuestType
    )
    {
        public string NameString()
        {
            if (DebugQuestType == DebugStageDetailQuestType.Pvp)
            {
                return ZString.Format(
                    "{0}\n{1}",
                    EventName.Value,
                    QuestName.Value);
            }

            if (EventName.IsEmpty())
            {
                return ZString.Format(
                    "{0} : {1}",
                    QuestName.Value,
                    Difficulty);
            }

            return ZString.Format(
                "{0} \n{1} : {2}",
                EventName.Value,
                QuestName.Value,
                Difficulty);
        }

        public QuestType ToQuestType()
        {
            return DebugQuestType switch
            {
                DebugStageDetailQuestType.Normal => QuestType.Normal,
                DebugStageDetailQuestType.Enhance => QuestType.Enhance,
                DebugStageDetailQuestType.Event => QuestType.Event,
                DebugStageDetailQuestType.Tutorial => QuestType.Tutorial,
                _ => QuestType.Normal,
            };
        }
    };

    public class DebugStageSummaryUseCase
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }

        public IReadOnlyList<DebugStageSummaryUseCaseModel> GetModels()
        {
            var questLists = MstQuestDataRepository.GetMstQuestModels()
                .Select(CreateFromQuest);
            var adventBattleLists = MstAdventBattleDataRepository.GetMstAdventBattleModels()
                .Select(CreateFromAdventBattle);
            var pvpLists = MstPvpDataRepository.GetMstPvpModels()
                .Select(CreateFromPvp);

            return questLists
                .Concat(adventBattleLists)
                .Concat(pvpLists)
                .OrderBy(m =>
                {
                    var orderQuestTypeList = new List<DebugStageDetailQuestType>
                    {
                        DebugStageDetailQuestType.Event,
                        DebugStageDetailQuestType.AdventBattle,
                        DebugStageDetailQuestType.Pvp,
                        DebugStageDetailQuestType.Normal,
                        DebugStageDetailQuestType.Enhance,
                        DebugStageDetailQuestType.Tutorial,
                    };
                    return orderQuestTypeList.IndexOf(m.DebugQuestType);
                })
                .ThenBy(x => x.MstEventIdForSort)
                .ThenBy(x => x.SortOrder)
                .ToList();
        }

        DebugStageSummaryUseCaseModel CreateFromQuest(MstQuestModel model)
        {
            var eventId = model.MstEventId.IsEmpty() ? MasterDataId.Empty : model.MstEventId;
            var mstEvent = eventId.IsEmpty()
                ? MstEventModel.Empty
                : MstEventDataRepository.GetEvent(eventId);

            return new DebugStageSummaryUseCaseModel(
                model.SortOrder,
                mstEvent.Id,
                model.Id,
                model.Name,
                mstEvent.Name,
                model.Difficulty,
                ToDebugStageDetailQuestType(model.QuestType)
            );
        }

        DebugStageDetailQuestType ToDebugStageDetailQuestType(QuestType questType)
        {
            return questType switch
            {
                QuestType.Event => DebugStageDetailQuestType.Event,
                QuestType.Normal => DebugStageDetailQuestType.Normal,
                QuestType.Enhance => DebugStageDetailQuestType.Enhance,
                QuestType.Tutorial => DebugStageDetailQuestType.Tutorial,
                _ => DebugStageDetailQuestType.Normal,
            };
        }

        DebugStageSummaryUseCaseModel CreateFromAdventBattle(MstAdventBattleModel model)
        {
            return new DebugStageSummaryUseCaseModel(
                SortOrder.Empty,
                MasterDataId.Empty,
                model.Id,
                new QuestName(model.AdventBattleName.Value),
                new EventName("降臨バトル"),
                Difficulty.Normal,
                DebugStageDetailQuestType.AdventBattle
            );
        }

        DebugStageSummaryUseCaseModel CreateFromPvp(MstPvpModel model)
        {
            return new DebugStageSummaryUseCaseModel(
                SortOrder.Empty,
                MasterDataId.Empty,
                model.Id, //本当はSysPvpSeasonIdが良いが同一内容が入るので使い回す
                new QuestName(model.Id.Value),
                new EventName("ランクマッチ"),
                Difficulty.Normal,
                DebugStageDetailQuestType.Pvp
            );
        }
    }
}
