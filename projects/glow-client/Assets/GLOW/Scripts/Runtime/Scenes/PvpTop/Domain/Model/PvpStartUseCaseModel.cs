using GLOW.Core.Domain.Models.Pvp;

namespace GLOW.Scenes.PvpTop.Domain.Model
{
    public record PvpStartUseCaseModel(OpponentPvpStatusModel OpponentPvpStatus)
    {
        public static PvpStartUseCaseModel Empty { get; } = new(OpponentPvpStatusModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
