using System;
using System.Collections.Generic;
using System.Linq;

namespace GLOW.Core.Domain.Models
{
    public record GameFetchModel(
        UserParameterModel UserParameterModel,
        IReadOnlyList<StageModel> StageModels,
        IReadOnlyList<UserStageEventModel> UserStageEventModels,
        IReadOnlyList<UserStageEnhanceModel> UserStageEnhanceModels,
        IReadOnlyList<UserAdventBattleModel> UserAdventBattleModels,
        UserBuyCountModel UserBuyCountModel,
        BadgeModel BadgeModel,
        MissionStatusModel MissionStatusModel)
    {
        public static GameFetchModel Empty { get; } = new GameFetchModel(
            UserParameterModel.Empty,
            new List<StageModel>(),
            new List<UserStageEventModel>(),
            new List<UserStageEnhanceModel>(),
            new List<UserAdventBattleModel>(),
            UserBuyCountModel.Empty,
            BadgeModel.Empty,
            MissionStatusModel.Empty);

        public virtual bool Equals(GameFetchModel other)
        {
            if (ReferenceEquals(this, other)) return true;
            if (other == null) return false;

            if (UserParameterModel != other.UserParameterModel) return false;
            if (UserBuyCountModel != other.UserBuyCountModel) return false;
            if (BadgeModel != other.BadgeModel) return false;
            if (MissionStatusModel != other.MissionStatusModel) return false;

            if ((StageModels == null) ^ (other.StageModels == null)) return false;
            if (StageModels != null && other.StageModels != null)
            {
                if (!StageModels.SequenceEqual(other.StageModels)) return false;
            }

            if ((UserStageEventModels == null) ^ (other.UserStageEventModels == null)) return false;
            if (UserStageEventModels != null && other.UserStageEventModels != null)
            {
                if (!UserStageEventModels.SequenceEqual(other.UserStageEventModels)) return false;
            }

            if ((UserStageEnhanceModels == null) ^ (other.UserStageEnhanceModels == null)) return false;
            if (UserStageEnhanceModels != null && other.UserStageEnhanceModels != null)
            {
                if (!UserStageEnhanceModels.SequenceEqual(other.UserStageEnhanceModels)) return false;
            }

            if ((UserAdventBattleModels == null) ^ (other.UserAdventBattleModels == null)) return false;
            if (UserAdventBattleModels != null && other.UserAdventBattleModels != null)
            {
                if (!UserAdventBattleModels.SequenceEqual(other.UserAdventBattleModels)) return false;
            }

            return true;
        }

        public override int GetHashCode()
        {
            HashCode hash = new();

            hash.Add(UserParameterModel);
            hash.Add(UserBuyCountModel);
            hash.Add(BadgeModel);
            hash.Add(MissionStatusModel);

            AddHashCodes(hash, StageModels);
            AddHashCodes(hash, UserStageEventModels);
            AddHashCodes(hash, UserStageEnhanceModels);
            AddHashCodes(hash, UserAdventBattleModels);

            return hash.ToHashCode();
        }

        static void AddHashCodes<T>(HashCode hash, IReadOnlyList<T> models)
        {
            if (models == null) return;

            int start = models.Count > 10 ? models.Count - 10 : 0;
            for (int i = start; i < models.Count; i++)
            {
                hash.Add(models[i]);
            }
        }
    };
}
