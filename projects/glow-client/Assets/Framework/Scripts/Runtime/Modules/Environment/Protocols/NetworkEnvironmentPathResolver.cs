using WonderPlanet.HashCalculator;

namespace WPFramework.Modules.Environment
{
    public sealed class NetworkEnvironmentPathResolver
    {
        readonly string _applicationVersion;
        readonly string _fixedIdentifier;
        readonly string _relativePath;
        readonly string _fileExtension;

        public NetworkEnvironmentPathResolver(string applicationVersion, string fixedIdentifier, string relativePath, string fileExtension)
        {
            _applicationVersion = applicationVersion;
            _fixedIdentifier = fixedIdentifier;
            _relativePath = relativePath;
            _fileExtension = fileExtension;
        }

        public string Resolve()
        {
            // NOTE: https://docs.google.com/spreadsheets/d/1v_JWIkorzjD2R8xfI34NSZ1r_ZumhT9tbnmO2FI-nY0/edit#gid=0
            var arrangeApplicationVersion = _applicationVersion.Replace(".", "_");
            var clientVersionHash = BuildClientVersionHash(_applicationVersion, _fixedIdentifier);
            return $"/{_relativePath}/{arrangeApplicationVersion}_{clientVersionHash}{_fileExtension}";
        }

        string BuildClientVersionHash(string applicationVersion, string fixedIdentifier)
        {
            // NOTE: https://wonderplanet.atlassian.net/wiki/spaces/SEED/pages/257130746/SEED+_
            var hashGenerator = new MD5HashGenerator();
            var arrangeApplicationVersion = applicationVersion.Replace(".", "_");
            var hashTarget = $"{applicationVersion}_{fixedIdentifier}";
            var hash = hashGenerator.GetHash(hashTarget);
            var targetString = $"{arrangeApplicationVersion}_{hash}";
            return hashGenerator.GetHash(targetString);
        }
    }
}
