using GLOW.Core.Data.Data.User;

namespace GLOW.Core.Data.DataStores
{
    public interface ISpecialAttackCutInLogLocalDataStore
    {
        SpecialAttackCutInLogData Load();
        void Save(SpecialAttackCutInLogData specialAttackCutInLogData);
    }
}